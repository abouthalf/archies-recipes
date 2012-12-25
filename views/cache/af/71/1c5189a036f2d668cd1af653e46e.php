<?php

/* page.twig */
class __TwigTemplate_af711c5189a036f2d668cd1af653e46e extends Twig_Template
{
    public function __construct(Twig_Environment $env)
    {
        parent::__construct($env);

        $this->parent = $this->env->loadTemplate("layout.twig");

        $this->blocks = array(
            'title' => array($this, 'block_title'),
            'meta' => array($this, 'block_meta'),
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
    public function block_meta($context, array $blocks = array())
    {
        // line 6
        echo "\t";
        $context['_parent'] = (array) $context;
        $context['_seq'] = twig_ensure_traversable((isset($context["meta"]) ? $context["meta"] : $this->getContext($context, "meta")));
        foreach ($context['_seq'] as $context["_key"] => $context["m"]) {
            // line 7
            echo "\t<meta name=\"";
            echo twig_escape_filter($this->env, $this->getAttribute((isset($context["m"]) ? $context["m"] : $this->getContext($context, "m")), "name"), "html", null, true);
            echo "\" content=\"";
            echo twig_escape_filter($this->env, $this->getAttribute((isset($context["m"]) ? $context["m"] : $this->getContext($context, "m")), "content"), "html", null, true);
            echo "\">
\t";
        }
        $_parent = $context['_parent'];
        unset($context['_seq'], $context['_iterated'], $context['_key'], $context['m'], $context['_parent'], $context['loop']);
        $context = array_merge($_parent, array_intersect_key($context, $_parent));
    }

    // line 13
    public function block_content($context, array $blocks = array())
    {
        // line 14
        echo "\t";
        if ((isset($context["image"]) ? $context["image"] : $this->getContext($context, "image"))) {
            // line 15
            echo "\t\t<aside>
\t\t\t<a href=\"images/";
            // line 16
            echo twig_escape_filter($this->env, (isset($context["image"]) ? $context["image"] : $this->getContext($context, "image")), "html", null, true);
            echo "\">
\t\t\t\t<img src=\"images/";
            // line 17
            echo twig_escape_filter($this->env, (isset($context["image"]) ? $context["image"] : $this->getContext($context, "image")), "html", null, true);
            echo "\" alt=\"";
            echo twig_escape_filter($this->env, (isset($context["title"]) ? $context["title"] : $this->getContext($context, "title")), "html", null, true);
            echo "\">
\t\t\t</a>
\t\t</aside>
\t";
        }
        // line 21
        echo "\t<article>
\t\t";
        // line 22
        echo strtr((isset($context["body"]) ? $context["body"] : $this->getContext($context, "body")), array("<body>" => "", "</body>" => ""));
        echo "
\t</article>
\t<nav>
\t\t";
        // line 25
        if (((isset($context["next"]) ? $context["next"] : $this->getContext($context, "next")) || (isset($context["prev"]) ? $context["prev"] : $this->getContext($context, "prev")))) {
            // line 26
            echo "\t\t<ul>
\t\t\t";
            // line 27
            if ((isset($context["prev"]) ? $context["prev"] : $this->getContext($context, "prev"))) {
                // line 28
                echo "\t\t\t<li>
\t\t\t\t<a href=\"";
                // line 29
                echo twig_escape_filter($this->env, (isset($context["prev"]) ? $context["prev"] : $this->getContext($context, "prev")), "html", null, true);
                echo "\">Previous</a>
\t\t\t</li>
\t\t\t";
            }
            // line 32
            echo "\t\t\t";
            if ((isset($context["next"]) ? $context["next"] : $this->getContext($context, "next"))) {
                // line 33
                echo "\t\t\t\t<li>
\t\t\t\t\t<a href=\"";
                // line 34
                echo twig_escape_filter($this->env, (isset($context["next"]) ? $context["next"] : $this->getContext($context, "next")), "html", null, true);
                echo "\">Next</a>
\t\t\t\t</li>
\t\t\t";
            }
            // line 37
            echo "\t\t</ul>
\t\t";
        }
        // line 39
        echo "\t</nav>
";
    }

    public function getTemplateName()
    {
        return "page.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  120 => 39,  116 => 37,  110 => 34,  107 => 33,  104 => 32,  98 => 29,  95 => 28,  93 => 27,  90 => 26,  88 => 25,  82 => 22,  79 => 21,  70 => 17,  66 => 16,  63 => 15,  60 => 14,  57 => 13,  44 => 7,  39 => 6,  36 => 5,  30 => 3,);
    }
}
