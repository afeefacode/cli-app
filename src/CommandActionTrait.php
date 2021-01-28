<?php

namespace Afeefa\Component\Cli;

use Symfony\Component\Console\Style\SymfonyStyle;
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
        $this->printText("<options=underscore>$title</>");
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

    protected function printBullet(string $text)
    {
        $this->io->text("* $text");
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

        $cwd = ($cwd ?? getcwd()) . '/';
        $command = preg_replace("~$cwd~", '', $command);
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

        $this->printText("<bg=red;fg=white>$spaces</>");
        foreach ($textArray as $text) {
            $spacesEnd = str_repeat(' ', $maxTextLength + 2 - strlen($text));
            $this->printText("<bg=red;fg=white>  $text$spacesEnd</>");
        }
        $this->printText("<bg=red;fg=white>$spaces</>");

        $this->printNewLine();
    }

    protected function printChoice(...$arguments)
    {
        return $this->io->choice(...$arguments);
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

        $this->printText("<fg=red>Abort: $text</>");
        $this->printNewLine();
        exit;
    }

    protected function indentText($text)
    {
        $text = preg_replace('/d/', 'TEST', $text);

        $text = preg_replace("/\n/", "----------------\n ", $text);
        $text = preg_replace('/ $/', '', $text);
        // $text = trim($text);
        return ' ------------- ' . $text;
    }

    /**
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

    /**
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

    protected function doRunAction(string $Action, array $args, string $cwd = null, $titleFormat = Action::TITLE_NORMAL)
    {
        $currentDir = getcwd();

        if ($cwd) {
            chdir($cwd);
        }

        $action = new $Action($this->io, $titleFormat);

        $result = $action->run($args);

        chdir($currentDir);

        return $result;
    }

    /**
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