<?php

namespace AppBundle\DQL;

use Doctrine\ORM\Query\AST\Functions\FunctionNode;
use Doctrine\ORM\Query\Lexer;
use Doctrine\ORM\Query\Parser;
use Doctrine\ORM\Query\SqlWalker;

/**
 * "REPLACE" "(" StringPrimary "," StringSecondary "," StringThird ")"
 *
 *
 * @link    www.prohoney.com
 * @since   2.0
 * @author  Igor Aleksejev
 */
class ReplaceFunction extends FunctionNode
{
    public $stringPrimary;
    public $stringSecondary;
    public $stringThird;

    /**
     * @override
     */
    public function getSql(SqlWalker $sqlWalker)
    {
        return 'REPLACE(' .
                    $this->stringPrimary->dispatch($sqlWalker) . ', ' .
                    $this->stringSecondary->dispatch($sqlWalker) . ', ' .
                    $this->stringThird->dispatch($sqlWalker) .
                ')';
        /*        return $sqlWalker->getConnection()->getDatabasePlatform()->getReplaceExpression(
                                $this->stringPrimary, $this->stringSecondary, $this->stringThird
                );*/
    }

    /**
     * @override
     */
    public function parse(Parser $parser)
    {
        $parser->match(Lexer::T_IDENTIFIER);
        $parser->match(Lexer::T_OPEN_PARENTHESIS);
        $this->stringPrimary = $parser->StringPrimary();
        $parser->match(Lexer::T_COMMA);
        $this->stringSecondary = $parser->StringPrimary();
        $parser->match(Lexer::T_COMMA);
        $this->stringThird = $parser->StringPrimary();
        $parser->match(Lexer::T_CLOSE_PARENTHESIS);
    }
}
