<?php
namespace Package\Difference\Fun\Parse\Service;

use Difference\Fun\App;

use Difference\Fun\Module\Cli;
use Difference\Fun\Module\Data;

use Plugin;

use Difference\Fun\Module\Core;
use Difference\Fun\Module\Dir;
use Difference\Fun\Module\File;

use Difference\Fun\Node\Model\Node;

use Exception;

use Difference\Fun\Exception\ObjectException;

class Parse
{
    const NODE = 'System.Parse';
    const CONFIG = 'package.difference_fun/parse';


    use Plugin\Basic;

    /**
     * @throws ObjectException
     * @throws Exception
     */
    public function __construct(App $object, Data $data, $flags, $options){
        $this->object($object);
        $this->data($data);
        $this->flags($flags);
        $this->options($options);
        //move to install (config)
        $this->config();
    }

    /**
     * @throws ObjectException
     * @throws Exception
     */
    protected function config(): void
    {
        $object = $this->object();
        $node = new Node($object);
        $parse = $node->record(
            Parse::NODE,
            $node->role_system(),
            [
                'ramdisk' => true
            ]
        );
        $options = $this->options();
        $force = false;
        if(property_exists($options,'force')){
            $parse = false;
            $force = true;
        }
        if(property_exists($options, 'patch')){
            $parse = false;
        }
        if(!$parse){
            $url = $object->config('project.dir.vendor') .
                'difference_fun' .
                $object->config('ds') .
                'parse' .
                $object->config('ds') .
                'Data' .
                $object->config('ds') .
                Parse::NODE .
                $object->config('extension.json')
            ;
            if($force){
                $options = (object) [
                    'url' => $url,
                    'force' => true
                ];
            } else {
                $options = (object) [
                    'url' => $url,
                    'patch' => true
                ];
            }
            $response = $node->import(Parse::NODE, $node->role_system(), $options);
            $parse = $node->record(
                Parse::NODE,
                $node->role_system(),
                [
                    'ramdisk' => true
                ]
            );
        }
        $object->config(Parse::CONFIG, $parse['node']);
        $object->config(Parse::CONFIG . '.time.start', microtime(true));
    }

    /**
     * @throws Exception
     */
    public function compile($input, $data=null){
        if(is_array($data)){
            $data = new Data($data);
            $this->data($data);
        }
        elseif(
            is_object($data) &&
            !($data instanceof Data)
        ){
            $data = new Data($data);
            $this->data($data);
        } else {
            $data = $this->data();
        }
        $object = $this->object();
        $flags = $this->flags();
        $options = $this->options();

        $options->class = $options->class ?? 'Main_' . hash('sha256', $input);
        $options->namespace = $options->namespace ?? 'Package\Difference\Fun\Parse';
        $dir = $object->config('project.dir.data') .
            'Test' .
            $object->config('ds') .
            'Parse' .
            $object->config('ds');
        Dir::create($dir, Dir::CHMOD);
        $token = Token::tokenize($object, $flags, $options, $input);

        $url = $dir . 'Main.json';
        File::write($url, Core::object($token, Core::OBJECT_JSON));

        $url = $dir . 'Main.php';

        $document = Build::create($object, $flags, $options, $token);

//        d($object->config('package'));

        File::write($url, implode(PHP_EOL, $document));
        File::permission(
            $object,
            [
                'dir' => $dir,
                'url' => $url
            ]
        );

        require_once $url;
        echo PHP_EOL . str_repeat('-', Cli::tput('columns')) . PHP_EOL;

        $run = new $options->namespace . '\\' . $options->class;

        $main = new \Package\Difference\Fun\Parse\Main($object, $this, $data, $flags, $options);
        return $main->run();

        /*
        // Step 2: Define the placeholder values
        $placeholders = [
            'name' => 'John Doe',
            'age' => '30',
            // Add more placeholders and their replacements as needed
        ];
        // Step 3: Replace placeholders with actual values
        foreach ($placeholders as $key => $value) {
            $template = str_replace('{{' . $key . '}}', $value, $template);
        }
        // Step 4: Output the processed template
        dd($template);
        */
    }

}