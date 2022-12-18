<?php
namespace App\Nginx;

class Lexer
{
    const EOF = -1; // represent end of file char

    protected $input;     // input string
    protected $index_input_char = 0;     // index into input of current character
    protected $current;         // current character

    const EXPR = 1;
    const LBRACKET = 2;
    const RBRACKET = 3;
    const SERVER = 4;
    const LOCATION = 5;

    static public $tokenNames = array("n/a", "<EOF>",
        "LBRACKET", "RBRACKET", "SERVER", "LOCATION" );

    public function __construct($input)
    {
        $this->input = $input;
        // prime lookahead
        $this->current = substr($input, $this->index_input_char, 1);
    }

    /** Move one character; detect "end of file" */
    public function consume()
    {
        $this->index_input_char++;
        if ($this->index_input_char >= strlen($this->input) - 1) {
            $this->current = self::EOF;
        } else {
            $this->current = substr($this->input, $this->index_input_char, 1);
        }
    }

    public function getTokenName($x)
    {
        return self::$tokenNames[$x];
    }


    public function isCharacter()
    {
        return $this->current != "{" &&
            $this->current != "}" &&
            $this->current != "\t" &&
            $this->current != "\n" &&
            $this->current != "\r";
    }

    public function nextToken()
    {
        while ($this->current != self::EOF) {
            switch ($this->current) {
//                case ' ':
                case "\t":
                case "\n":
                case "\r":
                    $this->ignoreWhitespaces();
                    continue 2;
                case '{':
                    $this->consume();
                    return new Token(self::LBRACKET, "{");
                    break;
                case '}':
                    $this->consume();
                    return new Token(self::RBRACKET, "}");
                    break;
                default:
                    $parsed = '';
                    do {
                        $parsed .= $this->current;
                        $this->consume();
                    } while ($this->isCharacter());

                    if ($var = $this->readWordAndCheck($parsed, self::SERVER, 'server')) {
                        return $var;
                    }

                    if ($var = $this->readWordAndCheck($parsed, self::LOCATION, 'location')) {
                        return $var;
                    }

                    return new Token(self::EXPR, $parsed);
                    //throw new \Exception("Not valid: " . $parsed);
            }
        }
        return new Token(self::EOF, "<EOF>");
    }

    public function readWordAndCheck($parsed, $symbol, $reservated_word)
    {
        if(preg_match('/^'.$reservated_word.'/', trim(strtolower($parsed)))) {
            return new Token($symbol, $parsed);
        } else {
            return false;
        }
//        if (strtolower(trim($parsed)) == $reservated_word) {
//            return new Token($symbol, $parsed);
//        } else {
//            return false;
//        }
    }

    public function isEOF()
    {
        return $this->current == '-1';
    }

    /** ignoreWhitespaces : (' '|'\t'|'\n'|'\r')* ; // ignore any whitespace */
    public function ignoreWhitespaces()
    {
        while (\ctype_space($this->current)) {
            $this->consume();
        }
    }
}
