<?php

/* 404.twig */
class __TwigTemplate_1f3e9b49a5e028652c223b797ca4d1fe extends Twig_Template
{
    public function __construct(Twig_Environment $env)
    {
        parent::__construct($env);

        $this->parent = $this->env->loadTemplate("layout.twig");

        $this->blocks = array(
            'title' => array($this, 'block_title'),
            'content' => array($this, 'block_content'),
        );
    }

    protected function doGetParent(array $context)
    {
        return "layout.twig";
    }

    protected function doDisplay(array $context, array $blocks = array())
    {
        $this->parent->display($context, array_merge($this->blocks, $blocks));
    }

    // line 3
    public function block_title($context, array $blocks = array())
    {
        echo "404";
    }

    // line 5
    public function block_content($context, array $blocks = array())
    {
        // line 6
        echo "<article>
\t<h1>Not found</h1>
\t<p>There is nothing at this address.</p>
\t<p><a href=\"index.html\">Start here.</p>
</article>
";
    }

    public function getTemplateName()
    {
        return "404.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  38 => 6,  35 => 5,  29 => 3,);
    }
}
