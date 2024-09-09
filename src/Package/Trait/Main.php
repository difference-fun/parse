<?php
namespace Package\Difference\Fun\Parse\Trait;

use Difference\Fun\App;
use Difference\Fun\Config;

use Difference\Fun\Module\Data;
use Difference\Fun\Module\Dir;
use Difference\Fun\Module\File;
use Difference\Fun\Module\Cli;

use Package\Difference\Fun\Parse\Service\Parse;
use Package\Difference\Fun\Parse\Service\Token;
use Package\Difference\Fun\Parse\Service\Build;

use Exception;


trait Main {

    /**
     * @throws Exception
     */
    public function compile($flags, $options): mixed {
        if(!property_exists($options, 'source')){
            throw new Exception('Source not found');
        }
        if(File::exist($options->source) === false){
            throw new Exception('Source not found');
        }
        $object = $this->object();
        $input = File::read($options->source);
        $parse = new Parse($object, new Data(), $flags, $options);
        echo $parse->compile($input);
        echo PHP_EOL . str_repeat('-', Cli::tput('columns')) . PHP_EOL;
        if(
            property_exists($options,'duration') &&
            $options->duration === true
        ){
            $result['duration'] = round((microtime(true) - $object->config('time.start')) * 1000, 2) . 'ms';
            return $result;
        }
        return null;
    }
}