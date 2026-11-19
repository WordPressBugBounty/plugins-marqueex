<?php


namespace Wpxero\Marqueex\Core;

defined('ABSPATH') || exit;

/**
 * Converts styles to CSS.
 *
 * Plain value object — instantiate one per property. It holds transient
 * device/hover state that parse() does not reset, so it must not be shared.
 */
class Property {


    /**
     * Raw name of the property without device or psuedo-class.
     *
     * @var string
     */
    public string $name;

    /**
     * Device type.
     *
     * @var string
     */
    public string $device = 'desktop';

    /**
     * Hover psuedo-class or not.
     *
     * @var bool
     */
    public bool $hover = false;

    /**
     * Constructor
     */
    public function __construct() {
        // No initialization required; fields default per declaration.
    }

    /**
     * Set property name and parse it
     *
     * @param string $property
     * @return void
     */
    public function set_property(string $property): void {
        $this->name = $property;
        $this->parse();
    }

    /**
     * Parse the fullname.
     *
     * @return void
     */
    private function parse(): void {
        if (str_starts_with($this->name, 'tablet')) {
            $this->name = lcfirst(substr($this->name, 6));
            $this->device = 'tablet';
        } elseif (str_starts_with($this->name, 'mobile')) {
            $this->name = lcfirst(substr($this->name, 6));
            $this->device = 'mobile';
        }

        if (str_ends_with($this->name, 'Hover')) {
            $this->name = substr($this->name, 0, strlen($this->name) - 5);
            $this->hover = true;
        }
    }
}
