<?php

/* archive.twig */
class __TwigTemplate_56fbb96140ea9cdfa3ad7f43494f6bbb extends Twig_Template
{
    public function __construct(Twig_Environment $env)
    {
        parent::__construct($env);

        $this->parent = $this->env->loadTemplate("layout.twig");

        $this->blocks = array(
            'title' => array($this, 'block_title'),
            'keywords' => array($this, 'block_keywords'),
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
        echo twig_escape_filter($this->env, (isset($context["title"]) ? $context["title"] : $this->getContext($context, "title")), "html", null, true);
    }

    // line 5
    public function block_keywords($context, array $blocks = array())
    {
        echo twig_escape_filter($this->env, (isset($context["keywords"]) ? $context["keywords"] : $this->getContext($context, "keywords")), "html", null, true);
    }

    // line 7
    public function block_content($context, array $blocks = array())
    {
        // line 8
        echo "<p>archive!</p>
\t<p>
\t\t";
        // line 10
        $context['_parent'] = (array) $context;
        $context['_seq'] = twig_ensure_traversable((isset($context["posts"]) ? $context["posts"] : $this->getContext($context, "posts")));
        foreach ($context['_seq'] as $context["_key"] => $context["post"]) {
            // line 11
            echo "\t\t\t";
            echo twig_escape_filter($this->env, (isset($context["post"]) ? $context["post"] : $this->getContext($context, "post")), "html", null, true);
            echo "<br>
\t\t";
        }
        $_parent = $context['_parent'];
        unset($context['_seq'], $context['_iterated'], $context['_key'], $context['post'], $context['_parent'], $context['loop']);
        $context = array_merge($_parent, array_intersect_key($context, $_parent));
        // line 13
        echo "\t</p>
";
    }

    public function getTemplateName()
    {
        return "archive.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  62 => 13,  53 => 11,  49 => 10,  45 => 8,  42 => 7,  36 => 5,  30 => 3,);
    }
}
