<?php

/* base.html */
class __TwigTemplate_3d4c1c690f9d8ed8df108d79d0a029ad17cd225d3ecbac0aa30920c66e92a39e extends Twig_Template
{
    public function __construct(Twig_Environment $env)
    {
        parent::__construct($env);

        $this->parent = false;

        $this->blocks = array(
            'head' => array($this, 'block_head'),
            'title' => array($this, 'block_title'),
            'content' => array($this, 'block_content'),
            'footer' => array($this, 'block_footer'),
        );
    }

    protected function doDisplay(array $context, array $blocks = array())
    {
        // line 1
        echo "<!DOCTYPE html>
<html>
    <head>
        ";
        // line 4
        $this->displayBlock('head', $context, $blocks);
        // line 8
        echo "    </head>
    <body>
        <div id=\"content\">";
        // line 10
        $this->displayBlock('content', $context, $blocks);
        echo "</div>
        <div id=\"footer\">
            ";
        // line 12
        $this->displayBlock('footer', $context, $blocks);
        // line 15
        echo "        </div>
    </body>
</html>";
    }

    // line 4
    public function block_head($context, array $blocks = array())
    {
        // line 5
        echo "            <!--<link rel=\"stylesheet\" href=\"style.css\" />-->
            <title>GLPI Telemetry - ";
        // line 6
        $this->displayBlock('title', $context, $blocks);
        echo "</title>
        ";
    }

    public function block_title($context, array $blocks = array())
    {
    }

    // line 10
    public function block_content($context, array $blocks = array())
    {
    }

    // line 12
    public function block_footer($context, array $blocks = array())
    {
        // line 13
        echo "                &copy; Copyright 2017 by <a href=\"http://glpi-project.org\">GLPI</a>.
            ";
    }

    public function getTemplateName()
    {
        return "base.html";
    }

    public function getDebugInfo()
    {
        return array (  71 => 13,  68 => 12,  63 => 10,  53 => 6,  50 => 5,  47 => 4,  41 => 15,  39 => 12,  34 => 10,  30 => 8,  28 => 4,  23 => 1,);
    }

    public function getSourceContext()
    {
        return new Twig_Source("", "base.html", "/var/www/html/glpi/telemetry/src/templates/base.html");
    }
}
