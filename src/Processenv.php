<?php

namespace Nexus633\Processenv;

use stdClass;

class Processenv
{

    /**
     * store options value
     * @var ProcessenvOptions
     */
    private ProcessenvOptions $options;

    /**
     * store the `.env`, `$_ENV` array
     * @var array
     */
    private array $env = [];

    /**
     * Option to add .env to global $_ENV and/or $_SERVER
     *
     * default
     * <code>
     *      $env = new Processenv(
     *          new ProcessenvOptions(
     *              localFirst: true,
     *              exceptions: true,
     *              globalEnv: false,
     *              globalServer: false,
     *              globalKey: false,
     *              objectParser: self::PARSE_AS_STDCLASS,
     *              replacePattern: '[:PROCESSENV_REPLACE:]'
     *          )
     *      );
     * </code>
     *
     *  If the <code>$globalKey</code> option is set to true,<br>
     *  a key is created in the globals $_SERVERS and $_ENV<br>
     *  where the .env file is then initialized.
     *
     * @param ?ProcessenvOptions $options
     */
    public function __construct(?ProcessenvOptions $options = null)
    {


        if (!$options || get_class($options) != 'Nexus633\Processenv\ProcessenvOptions') {
            $this->options = new ProcessenvOptions();
            return $this;
        }

        $this->options = $options;
        return $this;
    }

    /**
     * load and parse environment File
     * configure the parser and initialize processenv()
     *
     * @throws FileNotFoundException
     */
    public function load(): void
    {
        $this->parseEnvironmentFile();
        $this->configure();
    }

    /**
     * get environment key
     */
    public function processenv(string $value = '', mixed $default = ''): stdClass|string|array
    {
        if (empty($value) && empty($default)) {
            return $this->getAll();
        }
        return $this->get($value) ?: $default;
    }

    /**
     * get ´.env´ and ´$_ENV´ value if exists
     * else get empty string
     */
    protected function get(string $value): stdClass|string|array
    {
        if (array_key_exists($value, $this->env)) {
            return $this->env[$value];
        }

        return '';
    }

    /**
     * magic method to parse as object
     */
    public function __get(string $value): stdClass|string|array
    {
        if (array_key_exists($value, $this->env)) {
            return $this->env[$value];
        }

        return '';
    }

    /**
     * get all ´.env´ and ´$_ENV´ values
     */
    protected function getAll(): array
    {
        return $this->env;
    }

    /**
     * parse .env file
     * @throws FileNotFoundException
     */
    private function parseEnvironmentFile(): void
    {
        if ($this->options->isExceptions()) {
            $env_content_raw_array = $this->getEnvironmentFileContent();
        } else {
            try {
                $env_content_raw_array = $this->getEnvironmentFileContent();
            } catch (FileNotFoundException $fileNotFoundException) {
                $this->env = [];
                $this->configure();
                return;
            }
        }

        $env_content_without_blanks_and_comments = $this->filterEmptyLinesAndCommentLines($env_content_raw_array);
        $this->env = $this->setKeyValue($env_content_without_blanks_and_comments);
    }

    /**
     * Configure options
     */
    private function configure(): void
    {
        $_ENV = $_ENV ?: getenv();
        if ($this->options->isGlobalEnv()) {
            if ($this->options->isGlobalKey()) {
                $_ENV['DOTENV'] = $this->env;
            } else {
                $_ENV = array_merge($_ENV, $this->env);
            }
        }

        if ($this->options->isGlobalServer()) {
            if ($this->options->isGlobalKey()) {
                $_SERVER['DOTENV'] = $this->env;
            } else {
                $_SERVER = array_merge($_SERVER, $this->env);
            }
        }
        if ($this->options->isLocalFirst()) {
            $this->env = array_merge($this->env, $_ENV);
        } else {
            $this->env = array_merge($_ENV, $this->env);
        }

        unset($this->env["DOTENV"]);
    }

    /**
     * Parse `.env` file and set the key => value pairs
     */
    private function setKeyValue(array $content): array
    {
        $tmp_env = [];
        foreach ($content as $value) {
            [$key, $val] = explode('=', $value, 2);
            if (!empty($key) && !empty($val)) {
                $val = $this->removeQuotationMarks($val);
                $key = $this->removeQuotationMarks($key);
                $val = $this->filterInlineComments($val);
                $val = $this->parsNestedStrings($val, $tmp_env);
                $val = $this->parsObject($val);
                $tmp_env[$key] = $val;
            }
        }

        return $tmp_env;
    }

    /**
     * remove quotation marks
     */
    private function removeQuotationMarks($value): string
    {
        $value = trim($value);
        $value = preg_replace('/^"/', '', $value);
        $value = preg_replace('/"$/', '', $value);

        $value = preg_replace('/^\'/', '', $value);
        $value = preg_replace('/\'$/', '', $value);

        return trim($value);
    }

    /**
     * Parse nested string in `.env`
     */
    private function parsNestedStrings(string $value, array $tmp_array): string
    {
        preg_match_all('/\${[A-Z_]+}/', $value, $match);
        if (count($match[0]) > 0) {
            foreach ($match[0] as $match_keys) {
                $match_keys_tmp = preg_replace('/^\${/', '', $match_keys);
                $match_keys_tmp = preg_replace('/}$/', '', $match_keys_tmp);
                if (array_key_exists($match_keys_tmp, $tmp_array)) {
                    $value = str_replace($match_keys, $tmp_array[$match_keys_tmp], $value);
                }
            }
        }

        return $value;
    }

    /**
     * pars values to Object or Array
     */
    private function parsObject(string $value): stdClass|string|array
    {
        $associative = $this->options->getObjectParser();
        $parsedValue = json_decode($value, $associative);

        if (is_null($parsedValue)) {
            return $value;
        }

        return $parsedValue;
    }

    /**
     * get .env file content
     * @throws FileNotFoundException
     */
    private function getEnvironmentFileContent(): array
    {
        $_envFile = $_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . '.env';

        if (!file_exists($_envFile)) {
            echo 'FAILED';
            throw new FileNotFoundException('cant find environment file', 1);
        }

        return file($_envFile);
    }

    /**
     * filter line breaks from Array
     */
    private function filterEmptyLinesAndCommentLines(array $content): array
    {
        $content = array_filter($content, function ($value) {
            return !empty($value);
        });

        $content = array_filter($content, function ($value) {
            return !str_starts_with($value, '#');
        });

        return array_values($content);
    }

    /**
     * filter inline comments after escaped hashtag
     */
    private function filterInlineComments(string $value): string
    {
        if (!str_contains($value, '#')) {
            return $value;
        }

        if (!str_contains($value, '\#')) {
            $position = strpos($value, '#');
            return trim(substr($value, 0, $position));
        }

        $value = preg_replace('/(\\\#)/', $this->options->getReplacePattern(), $value);
        if (!str_contains($value, '#')) {
            return trim(str_replace($this->options->getReplacePattern(), '#', $value));
        }
        $position = strpos($value, '#');
        $value = trim(substr($value, 0, $position));
        $value = $this->removeQuotationMarks($value);

        return trim(str_replace($this->options->getReplacePattern(), '#', $value));
    }
}
