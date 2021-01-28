<?php

namespace Example;

use Afeefa\Component\Cli\Action;

class TestAction extends Action
{
    public function run(array $args = [], $cwd = null)
    {
        $this->printCommandTitle('This is a command title');

        $this->printText('Next is a new line');
        $this->printNewLine();
        $this->printText('That was a new line');

        $this->printActionTitle('This is an action title');

        $this->printList([
            'first list element',
            'second list element',
            'third list element'
        ]);

        $this->printCaution('Please be careful');

        $this->printBullet('This is a bullet');

        $this->printBullet('This is another bullet');

        $this->printShellCommand('docker start -d proxy php admin');

        $this->runProcess('ls -l --color="auto"');

        $result = $this->runProcessAndGetContents('ls -l --color="auto" c*');
        echo $result;

        $this->printNewLine();

        $this->printIndentedOutput(function () {
            echo 'this is a longer text hoho';
            echo "\n";
            echo 'this is a longer text hoho';
            echo "\n";
            echo "\n";
        });

        $this->printNewLine();
        $this->printNewLine();

        $this->printChoice('Select an option', [
            'this', 'or that', 'other option'
        ]);

        $this->printConfirm('Shall this be running?');
    }
}
