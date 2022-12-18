<?php
namespace App\Nginx;

class Token
{
    public $type;
    public $text;

    public function __construct($type, $text)
    {
        $this->type = $type;
        $this->text = $text;
    }

    public function __toString()
    {
        $tname = Lexer::$tokenNames[$this->type];
        return "<'" . $this->text . "'," . $tname . ">";
    }
}
