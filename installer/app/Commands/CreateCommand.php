<?php

namespace Orkestra\Skeleton\Commands;

use Orkestra\Interfaces\AppContainerInterface;
use Orkestra\Interfaces\ConfigurationInterface;
use Orkestra\Skeleton\Maker\MakerData;
use Orkestra\Skeleton\Maker\MakerExtension;
use Rakit\Validation\Validator;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\Question;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use Twig\Loader\LoaderInterface;

#[AsCommand(name: 'app:create', description: 'Create the application')]
class CreateCommand extends Command
{
	private InputInterface $input;
	private OutputInterface $output;

	public function __construct(
		private ConfigurationInterface $config,
		private AppContainerInterface $app,
		private MakerData $makerData,
		protected Validator $validator,
	) {
		parent::__construct();
	}

	protected function configure()
    {
        $this->addOption('replace', null, InputOption::VALUE_NONE, 'Replace current application');
    }

	protected function execute(InputInterface $input, OutputInterface $output)
    {
		$this->input = $input;
		$this->output = $output;

		$root = $this->config->get('root');

		$availableTemplates = array_values(array_diff(scandir($this->joinPath($root, 'templates')), ['.', '..']));

		$appTemplate = $availableTemplates[0];

		if (count($availableTemplates) > 1) {
			/** @var QuestionHelper */
			$helper = $this->getHelper('question');
			$question = new ChoiceQuestion(
				'<info>Please select a template</info>',
				$availableTemplates,
			);
			$question->setErrorMessage('Template "%s" is invalid.');

			$appTemplate = $helper->ask($input, $output, $question);
		}

		$tmpDir = $this->joinPath($root, 'tmp');
		$appTemplateDir = $this->joinPath($root, 'templates', $appTemplate);

		$this->app->bind(LoaderInterface::class, FilesystemLoader::class)->constructor(
			$appTemplateDir
        );

		$view = $this->app->get(Environment::class);
		$view->addExtension($this->app->get(MakerExtension::class));

		if (is_dir($tmpDir)) {
			$files = new \RecursiveIteratorIterator(
				new \RecursiveDirectoryIterator($tmpDir),
				\RecursiveIteratorIterator::CHILD_FIRST
			);

			foreach ($files as $file) {
				$source = $file->getPathname();

				if (str_ends_with($source, '.')) {
					continue;
				}

				if (is_dir($source)) {
					rmdir($source);
					continue;
				}

				unlink($source);
			}		
		} else {
			mkdir($tmpDir);
		}

		$templates = [];

		$files = new \RecursiveIteratorIterator(
			new \RecursiveDirectoryIterator($appTemplateDir)
		);

		foreach ($files as $file) {
			$source = $file->getPathname();

			if (is_dir($source)) {
				continue;
			}

			$destination = str_replace($appTemplateDir, $tmpDir, $source);
			$isTemplateFile = str_ends_with($source, '.twig') && substr_count($source, '.') > 1;

			if (!is_dir(dirname($destination))) {
				mkdir(dirname($destination), recursive: true);
			}

			if (!$isTemplateFile) {
				copy($source, $destination);
				continue;
			}

			$destination = substr($destination, 0, -5);
			$template = str_replace($appTemplateDir . DIRECTORY_SEPARATOR, '', $source);

			$view->render($template, ['renderData' => false]);

			$templates[] = [
				'template' => $template,
				'destination' => $destination,
			];
		}

		$questions = $this->makerData->getQuestions();

		if (!empty($questions)) {
			$output->writeln('Please answer the following questions:');

			foreach ($questions as $slug => $question) {
				$answer = $this->ask($question['title'], $question['description'], $this->makerData[$slug], $question['validation']);
				$this->makerData[$slug] = $answer;
			}
		}

		foreach ($templates as $template) {
			$content = $view->render($template['template'], ['renderData' => true]);
			file_put_contents($template['destination'], $content);
		}

		/**
         * @var bool
         */
        $replace = $input->getOption('replace');

		if ($replace) {
			$this->replaceRoot($root, $tmpDir);
		}

		$output->writeln('<info>Application created successfully!</info>');
        
        return Command::SUCCESS;
    }

	private function replaceRoot(string $root, string $tmpDir): void
	{
		$files = new \RecursiveIteratorIterator(
			new \RecursiveDirectoryIterator($root),
			\RecursiveIteratorIterator::CHILD_FIRST
		);

		foreach ($files as $file) {
			$source = $file->getPathname();

			if (str_ends_with($source, '.')) {
				continue;
			}

			if (str_starts_with($source, $tmpDir)) {
				continue;
			}

			if (is_dir($source)) {
				rmdir($source);
				continue;
			}

			unlink($source);
		}

		$files = new \RecursiveIteratorIterator(
			new \RecursiveDirectoryIterator($tmpDir),
			\RecursiveIteratorIterator::CHILD_FIRST
		);

		foreach ($files as $file) {
			$source = $file->getPathname();
			$destination = str_replace($tmpDir, $root, $source);

			if (str_ends_with($source, '.') || is_dir($source)) {
				continue;
			}

			if (!is_dir(dirname($destination))) {
				mkdir(dirname($destination), recursive: true);
			}

			copy($source, $destination);
		}

		foreach ($files as $file) {
			$source = $file->getPathname();
			if (str_ends_with($source, '.')) {
				continue;
			}
			if (is_dir($source)) {
				rmdir($source);
				continue;
			}
			unlink($source);
		}

		rmdir($tmpDir);
	}

	private function ask(string $title, string $description, ?string $default, string $validation): string
	{
		/** @var QuestionHelper */
		$helper = $this->getHelper('question');
		$question = new Question('<info>' . $title . '</info>: [' . $default . '] ', $default);

		$question->setAutocompleterValues([$default]);

		$question->setValidator(function ($answer) use ($title, $description, $default, $validation) {
			$validator = $this->validator->make([
				$title => $answer,
			], [
				$title => $validation,
			]);

			$validator->validate();

			if ($validator->fails()) {
				$this->output->writeln('<error>' . $validator->errors()->first($title) . '</error>');
				return $this->ask($title, $description, $default, $validation);
			}

			return $answer;
		});

		return $helper->ask($this->input, $this->output, $question);
	}

	private function joinPath(string ...$parts): string
	{
		return implode(DIRECTORY_SEPARATOR, $parts);
	}
}