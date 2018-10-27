<?php # -*- coding: utf-8 -*-

declare(strict_types=1);

namespace MultisiteGlobalMedia;

/**
 * Class SingleFilter
 */
class SingleFilter
{
    private $hook;

    private $callback;

    private $priority;

    public static function activate(string $hook, callable $callback, int $priority, int $args)
    {
        $self = new self($hook, $callback, $priority);
        add_filter($hook, [$self, 'apply'], $priority, $args);
    }

    private function __construct(string $hook, callable $callback, int $priority)
    {
        $this->hook = $hook;
        $this->callback = $callback;
        $this->priority = $priority;
    }

    public function apply(...$parameters)
    {
        $this->deactivate();
        return ($this->callback)(...$parameters);
    }

    private function deactivate()
    {
        remove_filter($this->hook, [$this, 'apply'], $this->priority);
    }
}
