<?php

use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Extension\SandboxExtension;
use Twig\Markup;
use Twig\Sandbox\SecurityError;
use Twig\Sandbox\SecurityNotAllowedTagError;
use Twig\Sandbox\SecurityNotAllowedFilterError;
use Twig\Sandbox\SecurityNotAllowedFunctionError;
use Twig\Source;
use Twig\Template;

/* sql/sql_query_results.twig */
class __TwigTemplate_dd1f3361e5acdfb994e6f43ca8d596b3 extends Template
{
    private $source;
    private $macros = [];

    public function __construct(Environment $env)
    {
        parent::__construct($env);

        $this->source = $this->getSourceContext();

        $this->parent = false;

        $this->blocks = [
        ];
    }

    protected function doDisplay(array $context, array $blocks = [])
    {
        $macros = $this->macros;
        // line 1
        echo "<div class=\"sqlqueryresults ajax\">
    ";
        // line 2
        echo ($context["previous_update_query"] ?? null);
        echo "
    ";
        // line 3
        echo ($context["profiling_chart"] ?? null);
        echo "
    ";
        // line 4
        echo ($context["missing_unique_column_message"] ?? null);
        echo "
    ";
        // line 5
        echo ($context["bookmark_created_message"] ?? null);
        echo "
    ";
        // line 6
        echo ($context["table"] ?? null);
        echo "
    ";
        // line 7
        echo ($context["bookmark_support"] ?? null);
        echo "
</div>
";
    }

    public function getTemplateName()
    {
        return "sql/sql_query_results.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  60 => 7,  56 => 6,  52 => 5,  48 => 4,  44 => 3,  40 => 2,  37 => 1,);
    }

    public function getSourceContext()
    {
        return new Source("", "sql/sql_query_results.twig", "/home/wp_q3unnj/betsierivercanoesandcampgroundcom.stage.site/wp-content/plugins/wp-phpmyadmin-extension/lib/phpMyAdmin_TuwErYUkJDe6mo2fPj1qzC5/templates/sql/sql_query_results.twig");
    }
}
