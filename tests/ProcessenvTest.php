<?php

use Nexus633\Processenv\FileNotFoundException;
use Nexus633\Processenv\Processenv;
use Nexus633\Processenv\ProcessenvOptions;
use PHPUnit\Framework\TestCase;

final class ProcessenvTest extends TestCase
{
    private ?Processenv $env = null;
    private ProcessenvOptions $envOptions;

    public function getProcessEnv(?ProcessenvOptions $options = null): Processenv
    {

        if ($options) {
            $this->env = new Processenv($options);
        } else {
            $this->env = new Processenv();
        }

        $this->env->load();

        return $this->env;
    }

    public function setUp(): void
    {
        $_SERVER['DOCUMENT_ROOT'] = dirname(__FILE__) . "/../";
    }

    /**
     * @doesNotPerformAssertions
     */
    public function testCantFindEnvFile(): void
    {
        try {
            $env = new Processenv();
            $env->processenv("MODE");
        } catch (FileNotFoundException $e) {
            $this->expectException(FileNotFoundException::class);
        }
    }

    /**
     * @return void
     */
    public function testGetValueByMagicFunction(): void
    {
        $excepted = 'live';

        $env = $this->getProcessEnv(new ProcessenvOptions(exceptions: false));
        $modeMagic = $env->MODE;
        $this->assertSame($excepted, $modeMagic);
    }

    /**
     * @return void
     */
    public function testGetObjectFromEnvFileAsStdClassByMagicFunction(): void
    {

        $env = $this->getProcessEnv(new ProcessenvOptions(exceptions: false, objectParser: ProcessenvOptions::PARSE_AS_STDCLASS));
        $stdClassObject = $env->ERROR_MODE;
        $this->assertInstanceOf(StdClass::class, $stdClassObject);
    }

    /**
     * @return void
     */
    public function testGetObjectValueFromEnvFileAsStdClassByMagicFunction(): void
    {
        $excepted = '/var/www/log/info.log';

        $env = $this->getProcessEnv(new ProcessenvOptions(exceptions: false, objectParser: ProcessenvOptions::PARSE_AS_STDCLASS));
        $stdClassObject = $env->ERROR_MODE->info;
        $this->assertSame($excepted, $stdClassObject);
    }

    /**
     * @return void
     */
    public function testGetObjectFromEnvFileAsArrayByMagicFunction(): void
    {
        $env = $this->getProcessEnv(new ProcessenvOptions(exceptions: false, objectParser: ProcessenvOptions::PARSE_AS_ARRAY));
        $stdClassObject = $env->ERROR_MODE;
        $this->assertIsArray($stdClassObject);
    }

    /**
     * @return void
     */
    public function testGetObjectValueFromEnvFileAsArrayByMagicFunction(): void
    {
        $excepted = 'info#with masked hash';

        $env = $this->getProcessEnv(new ProcessenvOptions(exceptions: false));
        $arrayObject = $env->ERROR_MODE_ARRAY[0];
        $this->assertSame($excepted, $arrayObject);
    }

    /**
     * @return void
     */
    public function testGetValueFromEnvFile(): void
    {
        $excepted = 'live';

        $env = $this->getProcessEnv(new ProcessenvOptions(exceptions: false));
        $mode = $env->processenv('MODE');
        $this->assertSame($excepted, $mode);
        $this->assertIsString($mode);
    }

    /**
     * @return void
     */
    public function testGetObjectFromEnvFileAsStdClass(): void
    {
        $env = $this->getProcessEnv(new ProcessenvOptions(exceptions: false, objectParser: ProcessenvOptions::PARSE_AS_STDCLASS));
        $stdClassObject = $env->processenv("ERROR_MODE");
        $this->assertInstanceOf(StdClass::class, $stdClassObject);
    }

    /**
     * @return void
     */
    public function testGetObjectFromEnvFileAsArray(): void
    {
        $env = $this->getProcessEnv(new ProcessenvOptions(exceptions: false, objectParser: ProcessenvOptions::PARSE_AS_ARRAY));
        $stdClassObject = $env->processenv("ERROR_MODE");
        $this->assertIsArray($stdClassObject);
    }

    /**
     * @return void
     */
    public function testGetValueFromEnvFileAsArray(): void
    {
        $env = $this->getProcessEnv(new ProcessenvOptions(exceptions: false));
        $arrayObject = $env->processenv("ERROR_MODE_ARRAY");
        $this->assertIsArray($arrayObject);
    }

    /**
     * @return void
     */
    public function testGetValueFromEnvFileAsArrayButIsString(): void
    {
        $env = $this->getProcessEnv(new ProcessenvOptions(exceptions: false));
        $arrayObject = $env->processenv("ERROR_MODE_WRONG_ARRAY");
        $this->assertIsString($arrayObject);
    }

    /**
     * @return void
     */
    public function testGetValueFromEnvFileInGlobalEnv(): void
    {
        $excepted = 'live';

        $this->getProcessEnv(new ProcessenvOptions(exceptions: false, globalEnv: true));
        $this->assertSame($excepted, $_ENV["MODE"]);
    }

    /**
     * @return void
     */
    public function testGetValueFromEnvFileInGlobalServer(): void
    {
        $excepted = 'live';

        $this->getProcessEnv(new ProcessenvOptions(exceptions: false, globalServer: true));
        $this->assertSame($excepted, $_SERVER["MODE"]);
    }

    /**
     * @return void
     */
    public function testGetValueFromEnvFileInGlobalEnvAsKeyArray(): void
    {
        $excepted = 'live';

        $this->getProcessEnv(new ProcessenvOptions(exceptions: false, globalEnv: true, globalKey: true));
        $this->assertIsArray($_ENV["DOTENV"]);
        $this->assertSame($excepted, $_ENV["DOTENV"]["MODE"]);
    }

    /**
     * @return void
     */
    public function testGetValueFromEnvFileInGlobalServerAsKeyArray(): void
    {
        $excepted = 'live';

        $this->getProcessEnv(new ProcessenvOptions(exceptions: false, globalServer: true, globalKey: true));
        $this->assertIsArray($_SERVER["DOTENV"]);
        $this->assertSame($excepted, $_SERVER["DOTENV"]["MODE"]);
    }

    /**
     * @return void
     */
    public function testGetAllValuesFromEnvFileAndGlobalEnv(): void
    {
        $env = $this->getProcessEnv(new ProcessenvOptions(exceptions: false));
        $this->assertNotNull($env->processenv());
        $this->assertNotEmpty($env->processenv());
    }

    /**
     * @return void
     */
    public function testGetDefaultValueByKeyNotExists(): void
    {
        $excepted = 'default value';

        $env = $this->getProcessEnv(new ProcessenvOptions(exceptions: false));
        $mode = $env->processenv('MODEN', $excepted);
        $this->assertSame($excepted, $mode);
    }

    /**
     * @return void
     */
    public function testGetEmptyStringByKeyNotExists(): void
    {
        $env = $this->getProcessEnv(new ProcessenvOptions(exceptions: false));
        $mode = $env->processenv('MODEN');
        $this->assertEmpty($mode);
    }

    /**
     * @return void
     */
    public function testGetInlineCommentValue(): void
    {
        $excepted = 'this is an inline comment';

        $env = $this->getProcessEnv(new ProcessenvOptions(exceptions: false));
        $inlineComment = $env->processenv('INLINE_COMMENT');
        $this->assertSame($excepted, $inlineComment);
    }

    /**
     * @return void
     */
    public function testGetInlineCommentWithMaskedHashValue(): void
    {
        $excepted = 'this is an inline comment # with masked hash';

        $env = $this->getProcessEnv(new ProcessenvOptions(exceptions: false));
        $inlineCommentWithMaskedHash = $env->processenv('INLINE_COMMENT_WITH_ESCAPE');
        $this->assertSame($excepted, $inlineCommentWithMaskedHash);
    }
}
