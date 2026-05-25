<?php

class doorGetsUserView
{
    public $doorGets = null;
    public function __construct(&$doorGets)
    {
        $this->doorGets = $doorGets;
        $this->Action = $doorGets->Action();
        $this->user = $doorGets->user;
        $this->doorGets = $doorGets;
    }
    public function getContent()
    {
        $out = '';
        switch ($this->Action) {
            case 'index':
                $out .= __CLASS__;
                break;
        }
        return $out;
    }
}
