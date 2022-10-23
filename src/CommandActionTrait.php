<?php

namespace Afeefa\Component\Cli;

use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Path;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;
use Twig\Environment;
use Twig\Loader\ArrayLoader;

trait CommandActionTrait
{
    /**
     * @var SymfonyStyle
     */
    protected $io;

    protected function printCommandTitle(string $title)
    {
        $this->io->title($title);
    }

    protected function printActionTitle(string $title)
    {
        $this->io->section($title);
    }

    protected function printSubActionTitle(string $title)
    {
        $this->printText("<options=underscore>{$title}</>");
        $this->printNewLine();
    }

    protected function printNewLine()
    {
        $this->io->newLine();
    }

    protected function printText(string $text)
    {
        $this->io->text($text);
    }

    protected function printInfo(string $text)
    {
        $this->io->text("\xF0\x9F\x9B\x88 {$text}");
    }

    protected function printBullet(string $text)
    {
        $this->io->text("* {$text}");
        $this->printNewLine();
    }

    protected function printList(array $list)
    {
        $this->io->listing($list);
    }

    protected function printShellCommand(string $command, $cwd = null)
    {
        $command = preg_replace('/ +/', ' ', $command);
        $this->printText('<fg=blue>$ cd ' . ($cwd ?? getcwd()) . '</>');

        $cwd = ($cwd ?? getcwd());

        $command = preg_replace_callback('/[\-\.\w\/]+/', function ($match) use ($cwd) {
            $pathIsInsideCwd = Path::isBasePath($cwd, $match[0]);
            if ($pathIsInsideCwd) {
                return Path::makeRelative($match[0], $cwd);
            }
            return $match[0];
        }, $command);

        $command = preg_replace('/\n/', ' ', $command);

        $this->printText('<fg=blue>$ ' . $command . '</>');

        $this->printNewLine();
    }

    protected function printCaution(string $text)
    {
        $textArray = explode("\n", $text);

        $maxTextLength = 0;
        foreach ($textArray as $text) {
            $maxTextLength = max(strlen($text), $maxTextLength);
        }

        $spaces = str_repeat(' ', $maxTextLength + 4);

        $this->printText("<bg=red;fg=white>{$spaces}</>");
        foreach ($textArray as $text) {
            $spacesEnd = str_repeat(' ', $maxTextLength + 2 - strlen($text));
            $this->printText("<bg=red;fg=white>  {$text}{$spacesEnd}</>");
        }
        $this->printText("<bg=red;fg=white>{$spaces}</>");

        $this->printNewLine();
    }

    protected function printChoice(...$arguments)
    {
        return $this->io->choice(...$arguments);
    }

    protected function printMultichoice(...$arguments)
    {
        $q = new ChoiceQuestion(...$arguments);
        $q->setMultiselect(true);
        $result = $this->io->askQuestion($q);
        return array_unique($result);
    }

    protected function printQuestion(string $question, $default = null)
    {
        return $this->io->ask($question, $default);
    }

    protected function printConfirm(...$arguments)
    {
        return $this->io->confirm(...$arguments);
    }

    protected function printIndentedOutput($callback)
    {
        ob_start();
        $callback();
        $content = ob_get_clean();
        $content = preg_replace("/\n/", "\n ", $content);
        $content = preg_replace('/ $/', '', $content);
        $content = trim($content);
        echo ' ' . $content;
    }

    protected function abortCommand($text = null)
    {
        $text = $text ?: $this->taskInfo;

        $this->printText("<fg=red>Abort: {$text}</>");
        $this->printNewLine();
        exit;
    }

    /*
     * Process
     */

    protected function runProcess($command, string $cwd = null, array $env = [])
    {
        $this->doRunProcess($command, $cwd, $env);
    }

    protected function runProcesses($commands, string $cwd = null, array $env = [])
    {
        foreach ($commands as $command) {
            $this->doRunProcess($command, $cwd, $env);
        }
    }

    protected function runProcessAndGetContents($command, string $cwd = null, array $env = []): string
    {
        return $this->doRunProcess($command, $cwd, $env, true);
    }

    private function doRunProcess($command, string $cwd = null, array $env = [], $hideOutput = false): string
    {
        if (is_string($command)) {
            $command = preg_replace("/\n+/", ' ', $command);
            $command = preg_replace('/ +/', ' ', $command);
        }

        $process = is_array($command)
            ? new Process($command, $cwd, $env)
            : Process::fromShellCommandline($command, $cwd, $env);

        if (!$hideOutput) {
            $commandString = is_array($command) ? implode(' ', $command) : $command;
            $this->printShellCommand($commandString, $cwd);

            $process->setPty(true);
        }

        $process->setTimeout(3600);

        $hasOutput = false;
        $process->run(function ($type, $buffer) use ($hideOutput, &$hasOutput) {
            $hasOutput = true;
            if (!$hideOutput) {
                echo $buffer;
            }
        });

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        if (!$hideOutput && $hasOutput) {
            $this->printNewLine();
        }

        return $process->getOutput();
    }

    /*
     * Action
     */

    protected function runAction(string $Action, array $args = [], string $cwd = null)
    {
        return $this->doRunAction($Action, $args, $cwd, Action::TITLE_NORMAL);
    }

    protected function runSubAction(string $Action, array $args = [], string $cwd = null)
    {
        return $this->doRunAction($Action, $args, $cwd, Action::TITLE_SMALL);
    }

    protected function runActionWithoutTitle(string $Action, array $args = [], string $cwd = null)
    {
        return $this->doRunAction($Action, $args, $cwd, Action::TITLE_HIDDEN);
    }

    private function doRunAction(string $Action, array $args, string $cwd = null, $titleFormat = Action::TITLE_NORMAL)
    {
        $currentDir = getcwd();

        if ($cwd) {
            chdir($cwd);
        }

        $action = new $Action($this->io, $titleFormat);

        $result = $action->run($args);

        chdir($currentDir);

        return $result ?: 0;
    }

    /*
     * File replace
     */

    protected function renderFile(string $file, array $data)
    {
        $template = file_get_contents($file);

        $twigString = new Environment(new ArrayLoader());
        $template = $twigString->createTemplate($template);
        $content = $template->render($data);

        $content = preg_replace("/\n\n$/", "\n", $content);
        file_put_contents($file, $content);
    }

    protected function replaceInFile(string $file, \Closure $callback)
    {
        $content = file_get_contents($file);
        $content = $callback($content);
        file_put_contents($file, $content);
    }
}
