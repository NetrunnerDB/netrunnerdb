<?php

namespace AppBundle\DQL;

use Doctrine\ORM\Query\AST\Functions\FunctionNode;
use Doctrine\ORM\Query\Lexer;
use Doctrine\ORM\Query\Parser;
use Doctrine\ORM\Query\SqlWalker;

/**
 * "BINARY" "(" StringPrimary ")"
 */
class BinaryFunction extends FunctionNode
{
    public $stringPrimary;

    /**
     * @override
     */
    public function getSql(SqlWalker $sqlWalker)
    {
        return 'binary ' . $this->stringPrimary->dispatch($sqlWalker);
    }

    /**
     * @override
     */
    public function parse(Parser $parser)
    {
        $parser->match(Lexer::T_IDENTIFIER);
        $parser->match(Lexer::T_OPEN_PARENTHESIS);
        $this->stringPrimary = $parser->StringPrimary();
        $parser->match(Lexer::T_CLOSE_PARENTHESIS);
    }
}
