<?php

namespace Eslider\Filter;

use Assetic\Filter\Sass\SassFilter;

/**
 *
 * @author Andriy Oblivantsev <eslider@gmail.com>
 */
class ScssFilter extends SassFilter
{
    /**
     * ScssFilter constructor.
     *
     * @param string $sassPath
     * @param null   $rubyPath
     */
    public function __construct($sassPath = '/usr/bin/sass', $rubyPath = null)
    {
        parent::__construct($sassPath, $rubyPath);
        $this->setScss(true);
    }
}