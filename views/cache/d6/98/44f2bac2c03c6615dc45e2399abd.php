<?php

/* layout.twig */
class __TwigTemplate_d69844f2bac2c03c6615dc45e2399abd extends Twig_Template
{
    public function __construct(Twig_Environment $env)
    {
        parent::__construct($env);

        $this->parent = false;

        $this->blocks = array(
            'title' => array($this, 'block_title'),
            'meta' => array($this, 'block_meta'),
            'content' => array($this, 'block_content'),
        );
    }

    protected function doDisplay(array $context, array $blocks = array())
    {
        // line 1
        echo "<!DOCTYPE html>
<!--[if lt IE 7]> <html class=\"no-js lt-ie9 lt-ie8 lt-ie7\" lang=\"en\"> <![endif]-->
<!--[if IE 7]>    <html class=\"no-js lt-ie9 lt-ie8\" lang=\"en\"> <![endif]-->
<!--[if IE 8]>    <html class=\"no-js lt-ie9\" lang=\"en\"> <![endif]-->
<!--[if gt IE 8]><!--> <html class=\"no-js\" lang=\"en\"> <!--<![endif]-->
\t<head>
\t\t<title>";
        // line 7
        $this->displayBlock('title', $context, $blocks);
        echo "</title>
\t\t";
        // line 8
        $this->displayBlock('meta', $context, $blocks);
        // line 9
        echo "\t\t<link rel=\"stylesheet\" href=\"/css/styles.css\" media=\"all\">
\t\t<script src=\"/js/site-min.js\"></script>
\t</head>
\t<body>
\t\t<header>
\t\t\t<h1>
\t\t\t\t<a href=\"/index.html\">
\t\t\t\t\tArchie's recipe book
\t\t\t\t</a>
\t\t\t</h1>
\t\t</header>
\t\t";
        // line 20
        $this->displayBlock('content', $context, $blocks);
        // line 21
        echo "\t\t<footer>
\t\t\tBrought to you by <a href=\"http://abouthalf.com\" target=\"_blank\">Abouthalf.com</a>
\t\t</footer>
\t</body>
</html>";
    }

    // line 7
    public function block_title($context, array $blocks = array())
    {
    }

    // line 8
    public function block_meta($context, array $blocks = array())
    {
    }

    // line 20
    public function block_content($context, array $blocks = array())
    {
    }

    public function getTemplateName()
    {
        return "layout.twig";
    }

    public function getDebugInfo()
    {
        return array (  69 => 20,  64 => 8,  59 => 7,  51 => 21,  49 => 20,  34 => 8,  22 => 1,  120 => 39,  116 => 37,  110 => 34,  107 => 33,  104 => 32,  98 => 29,  95 => 28,  93 => 27,  90 => 26,  88 => 25,  82 => 22,  79 => 21,  70 => 17,  66 => 16,  63 => 15,  60 => 14,  57 => 13,  44 => 7,  39 => 6,  36 => 9,  30 => 7,);
    }
}
