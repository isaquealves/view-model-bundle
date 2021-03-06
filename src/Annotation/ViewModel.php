<?php

/**
 * This file is part of view-model-bundle
 *
 * (c) Aaron Scherer <aequasi@gmail.com>
 *
 * This source file is subject to the license that is bundled
 * with this source code in the file LICENSE
 */

namespace Aequasi\Bundle\ViewModelBundle\Annotation;

/**
 * @author Aaron Scherer <aequasi@gmail.com>
 *
 * @Annotation
 */
class ViewModel
{
    /**
     * @type string
     */
    private $class;

    /**
     * @type string
     */
    private $service;

    /**
     * @param array $data
     *
     * @throws \Exception
     */
    public function __construct(array $data)
    {
        if (count(array_keys($data)) > 1) {
            throw new \Exception(
                "Your ViewModel declaration should not have named variables. ".
                "Just pass your class/service, and arguments (if any)."
            );
        }

        if (!isset($data['value'])) {
            $data['value'] = 'Aequasi\Bundle\ViewModelBundle\View\Model\HtmlViewModel';
        }

        $this->parseValue($data['value']);
    }

    /**
     * @return string
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * @return string
     */
    public function getService()
    {
        return $this->service;
    }

    /**
     * @return bool
     */
    public function hasClass()
    {
        return isset($this->class);
    }

    /**
     * @return bool
     */
    public function hasService()
    {
        return isset($this->service);
    }

    /**
     * @param string|array $value
     *
     * @return void
     *
     * @throws \Exception
     */
    private function parseValue($value)
    {
        if (strpos($value, '@') === 0) {
            $this->service = ltrim($value, '@');

            return;
        }

        if (!class_exists($value)) {
            throw new \Exception("Class \"{$value}\" does not exist.");
        }

        $this->class = $value;

        return;
    }
}
