<?php
/*
Plugin Name: Taxonomy Dictionary
Description: A plugin to generate an alphabetical dictionary menu based on a specified taxonomy.
Version: 1.0
Author: Your Name
*/

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

class TaxonomyDictionary {
    
    public function __construct() {
        add_shortcode('taxonomy_dictionary', [$this, 'generate_taxonomy_dictionary']);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_styles']);
    }

    public function enqueue_styles() {
        wp_register_style('taxonomy_dictionary_styles', false);
        wp_enqueue_style('taxonomy_dictionary_styles');
        wp_add_inline_style('taxonomy_dictionary_styles', $this->get_custom_styles());
    }

    private function get_custom_styles() {
        return "
        .taxonomy-dictionary .alphabet-block {
            display: flex;
            flex-direction: column;
        }
        .taxonomy-dictionary .alphabet-section {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr 1fr 1fr;
            gap: 15px;
            padding: 15px 0;
            border-bottom: 1px solid #ddd;
        }
        .taxonomy-dictionary .alphabet-letter {
            grid-column: 1;
            font-weight: bold;
            font-size: 1.5em;
            padding-right: 15px;
            display: flex;
            align-items: center;
        }
        .taxonomy-dictionary ul {
            grid-column: 2 / 6;
            list-style: none;
            padding: 0;
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 10px;
        }
        .taxonomy-dictionary li {
            margin: 0;
            padding: 5px 0;
        }
        .taxonomy-dictionary a {
            text-decoration: none;
            color: #333;
            padding: 5px;
            display: inline-block;
            border-radius: 4px;
            transition: background-color 0.3s ease;
        }
        .taxonomy-dictionary a:hover {
            color: #000;
        }

        /* Responsive adjustments */
        @media (max-width: 992px) { /* Tablet: 3 columns */
            .taxonomy-dictionary .alphabet-section {
                grid-template-columns: 1fr 1fr 1fr 1fr;
            }
            .taxonomy-dictionary ul {
                grid-template-columns: repeat(3, 1fr);
            }
        }

        @media (max-width: 768px) { /* Mobile: 2 columns */
            .taxonomy-dictionary .alphabet-section {
                grid-template-columns: 1fr 1fr 1fr;
            }
            .taxonomy-dictionary ul {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        ";
    }

    public function generate_taxonomy_dictionary($atts) {
        $atts = shortcode_atts(array(
            'taxonomy' => false,
        ), $atts);

        if (empty($atts['taxonomy'])) {
            return 'Taxonomy not passed.';
        }

        $terms = get_terms(array(
            'taxonomy' => $atts['taxonomy'],
            'hide_empty' => false,
            'orderby' => 'name',
            'order' => 'ASC'
        ));

        if (empty($terms) || is_wp_error($terms)) {
            return 'No terms found.';
        }

        $sorted_terms = [];
        foreach ($terms as $term) {
            $first_char = mb_strtoupper(mb_substr($term->name, 0, 1, "UTF-8"));
            if (!preg_match('/[A-ZА-ЯЁІЇЄҐ]/u', $first_char)) {
                $first_char = '#';
            }
            $sorted_terms[$first_char][] = $term;
        }

        $output = '<div class="taxonomy-dictionary"><div class="alphabet-block">';
        foreach ($sorted_terms as $letter => $terms) {
            $output .= '<div class="alphabet-section">';
            $output .= '<div class="alphabet-letter"><h3>' . $letter . '</h3></div>';
            $output .= '<ul>';
            foreach ($terms as $term) {
                $term_link = get_term_link($term);
                if (is_wp_error($term_link)) {
                    continue;
                }
                $output .= sprintf('<li><a href="%s">%s (%d)</a></li>', esc_url($term_link), esc_html($term->name), esc_html($term->count));
            }
            $output .= '</ul></div>';
        }
        $output .= '</div></div>';

        return $output;
    }
}

new TaxonomyDictionary();
