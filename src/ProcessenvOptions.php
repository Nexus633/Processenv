<?php

namespace Nexus633\Processenv;

class ProcessenvOptions
{

    /**
     * if this option is set, objects in the .env file will be converted to a StdClass.
     */
    const PARSE_AS_STDCLASS = 0;

    /**
     * if this option is set, objects in the .env file will be converted into an array.
     */
    const PARSE_AS_ARRAY = 1;

    /**
     * Option for Processenv class
     * There are also Pars options to configure the parser for Object
     * and masked hashtags (\\#)
     *
     * default
     * <code>
     *      new ProcessenvOptions(
     *          localFirst: true,
     *          exceptions: true,
     *          globalEnv: false,
     *          globalServer: false,
     *          globalKey: false,
     *          objectParser: self::PARSE_AS_STDCLASS,
     *          replacePattern: '[:PROCESSENV_REPLACE:]'
     *      );
     * </code>
     *
     */
    public function __construct(
        private bool   $localFirst = true,
        private bool   $exceptions = true,
        private bool   $globalEnv = false,
        private bool   $globalServer = false,
        private bool   $globalKey = false,
        private string $objectParser = self::PARSE_AS_STDCLASS,
        private string $replacePattern = '[:PROCESSENV_REPLACE:]'
    )
    {

    }

    /**
     * get local first option
     *
     */
    public function isLocalFirst(): bool
    {
        return $this->localFirst;
    }

    /**
     * get exceptions option
     *
     */
    public function isExceptions(): bool
    {
        return $this->exceptions;
    }

    /**
     * get glovalEnv option
     *
     */
    public function isGlobalEnv(): bool
    {
        return $this->globalEnv;
    }

    /**
     * get globalServer option
     *
     */
    public function isGlobalServer(): bool
    {
        return $this->globalServer;
    }

    /**
     * get globalKey
     *
     */
    public function isGlobalKey(): bool
    {
        return $this->globalKey;
    }

    /**
     * get objectParser option
     *
     */
    public function getObjectParser(): bool
    {
        return $this->objectParser == self::PARSE_AS_ARRAY;
    }

    /**
     * get replacePattern option
     *
     */
    public function getReplacePattern(): string
    {
        return $this->replacePattern;
    }
}
