<?php

namespace Nexus633\Dotenv;

use Nexus633\Dotenv\Exception\FileNotFoundException;

class ParseEnvironmentFile
{
    /**
     * store options value
     * @var array
     */
    private array $options;

    /**
     * store the `.env`, `$_ENV` array
     * @var array
     */
    private array $env;

    /**
     * get instance of ParseEnvironmentFile Class
     *
     * @param array $options
     * @throws FileNotFoundException
     */
    public function __construct(array $options)
    {
        $this->options = $options;
        $this->setDefaultOptions();
        $this->parseEnvironmentFile();
        $this->configure();
    }

    /**
     * set default options if not set
     * <code>
     *     $this->options['set_locale_first'] = true;
     *     $this->options['set_exceptions'] = true;
     *     $this->options['set_global_env'] = false;
     *     $this->options['set_global_server'] = false;
     *     $this->options['set_global_key'] = false;
     * </code>
     *
     * @return void
     */
    private function setDefaultOptions(): void{
        $this->options['set_locale_first'] = $this->options['set_locale_first'] ?? true;
        $this->options['set_exceptions'] = $this->options['set_exceptions'] ?? true;
        $this->options['set_global_env'] = $this->options['set_global_env'] ?? false;
        $this->options['set_global_server'] = $this->options['set_global_server'] ?? false;
        $this->options['set_global_key'] = $this->options['set_global_key'] ?? false;
    }

    /**
     * get ´.env´ and ´$_ENV´ value if exists
     * else get empty string
     *
     * @param string $value
     * @return mixed
     */
    protected function get(string $value): mixed
    {
        if(array_key_exists($value, $this->env)){
            return $this->env[$value];
        }

        return '';
    }

    /**
     * magic method to parse as object
     *
     * @param string $value
     * @return mixed
     */
    public function __get(string $value): mixed
    {
        if(array_key_exists($value, $this->env)){
            return $this->env[$value];
        }

        return '';
    }

    /**
     * get all ´.env´ and ´$_ENV´ values
     *
     * @return array
     */
    protected function getAll(): array {
        return $this->env;
    }

    /**
     * parse .env file
     *
     * @throws FileNotFoundException
     */
    private function parseEnvironmentFile(): void {

        if($this->options['set_exceptions']){
            $env_content_string = $this->getEnvironmentFileContent();
        }else{
            try {
                $env_content_string = $this->getEnvironmentFileContent();
            }catch (FileNotFoundException $fileNotFoundException){
                $this->env = [];
                $this->configure();
                return;
            }
        }

            $env_content_raw_array = preg_split('/\n|\r\n?/', $env_content_string);
            $env_content_without_blanks = $this->filterEmptyLines($env_content_raw_array);
            $env_content_without_blanks_comments = $this->filterCommentLines($env_content_without_blanks);
            $this->env = $this->setKeyValue($env_content_without_blanks_comments);

    }

    /**
     * Configure options
     *
     * @return void
     */
    private function configure(): void {
        $_ENV = $_ENV ?: getenv();
        if($this->options['set_global_env']){
            if($this->options['set_global_key']){
                $_ENV['DOTENV'] = $this->env;
            }else{
                $_ENV = array_merge($_ENV, $this->env);
            }
        }

        if($this->options['set_global_server']){
            if($this->options['set_global_key']) {
                $_SERVER['DOTENV'] = $this->env;
            }else{
                $_SERVER = array_merge($_SERVER, $this->env);
            }
        }
        if($this->options['set_locale_first']){
            $this->env = array_merge($this->env, $_ENV);
        }else{
            $this->env = array_merge($_ENV, $this->env);
        }

        unset($this->env["DOTENV"]);
    }

    /**
     * Parse `.env` file and set the key => value pairs
     *
     * @param array $content
     * @return array
     */
    private function setKeyValue(array $content): array
    {
        $tmp_env = [];
        foreach ($content as $value){
            [ $key, $val ] =  explode('=', $value, 2);
            if(!empty($key) && !empty($val)){
                $val = $this->removeQuotationMarks($val);
                $key = $this->removeQuotationMarks($key);
                $val = $this->parseNestedStrings($val, $tmp_env);

                $tmp_env[$key] = $val;
            }
        }

        return $tmp_env;
    }

    /**
     * remove quotation marks
     *
     * @param $value
     * @return string
     */
    private function removeQuotationMarks($value) : string
    {
        $value = preg_replace('/^"/', '', $value);
        $value = preg_replace('/"$/', '', $value);

        $value = preg_replace('/^\'/', '', $value);
        $value = preg_replace('/\'$/', '', $value);

        return trim($value);
    }

    /**
     * Parse nested string in `.env`
     *
     * @param string $value
     * @param array $tmp_array
     * @return string
     */
    private function parseNestedStrings(string $value, array $tmp_array): string
    {
        preg_match_all('/\${[A-Z_]+}/', $value, $match);
        if(count($match[0]) > 0){
            foreach($match[0] as $match_keys){
                $match_keys_tmp = preg_replace('/^\${/', '', $match_keys);
                $match_keys_tmp = preg_replace('/}$/', '', $match_keys_tmp);
                if(array_key_exists($match_keys_tmp, $tmp_array)){
                    $value = str_replace($match_keys, $tmp_array[$match_keys_tmp], $value);
                }
            }
        }

        return $value;
    }

    /**
     * get .env file content
     *
     * @return string
     * @throws FileNotFoundException
     */
    private function getEnvironmentFileContent(): string
    {
        $_envFile = $_SERVER['DOCUMENT_ROOT'].DIRECTORY_SEPARATOR.'.env';

        if(!file_exists($_envFile)){
            throw new FileNotFoundException('cant find environment file', 1);
        }

        return file_get_contents($_envFile);
    }

    /**
     * filter line breaks from Array
     *
     * @param array $content
     * @return array
     */
    private function filterEmptyLines(array $content): array
    {
        $content = array_filter($content, function($value) {
            return !empty($value);
        });

        return array_values($content);
    }

    /**
     * filter comment `#` lines from Array
     *
     * @param array $content
     * @return array
     */
    private function filterCommentLines(array $content): array
    {
        $content = array_filter($content, function($value) {
            return !str_starts_with($value, '#');
        });

        return array_values($content);
    }
}
