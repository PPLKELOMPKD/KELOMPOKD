<?php

namespace Tests;

use Laravel\Dusk\Browser;

class SlowBrowser extends Browser
{
    protected function pauseIfVisual(): void
    {
        if (isset($_ENV['DUSK_HEADLESS_DISABLED']) || isset($_SERVER['DUSK_HEADLESS_DISABLED'])) {
            $this->pause(600);
        }
    }

    public function visit($url)
    {
        $result = parent::visit($url);
        $this->pauseIfVisual();
        return $result;
    }

    public function type($field, $value)
    {
        $result = parent::type($field, $value);
        $this->pauseIfVisual();
        return $result;
    }

    public function click($selector = null)
    {
        $result = parent::click($selector);
        $this->pauseIfVisual();
        return $result;
    }

    public function press($button)
    {
        $result = parent::press($button);
        $this->pauseIfVisual();
        return $result;
    }

    public function check($field, $value = null)
    {
        $result = parent::check($field, $value);
        $this->pauseIfVisual();
        return $result;
    }

    public function uncheck($field, $value = null)
    {
        $result = parent::uncheck($field, $value);
        $this->pauseIfVisual();
        return $result;
    }

    public function select($field, $value = null)
    {
        $result = parent::select($field, $value);
        $this->pauseIfVisual();
        return $result;
    }

    public function attach($field, $path)
    {
        $result = parent::attach($field, $path);
        $this->pauseIfVisual();
        return $result;
    }
}
