<?php

namespace Drupal\entajax\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

class Selects extends FormBase {

  // buildForm() gets calleb by default
  public function buildForm(array $form, FormStateInterface $form_state) {
    $select_a = $form_state->getValue(['select_wrapper', 'select_a'], 'none');
    $select_b = $form_state->getValue(['select_wrapper', 'select_b'], 'none');
    $select_c = $form_state->getValue(['select_wrapper', 'select_c'], 'none');
    
    self::dump('buildForm', [
      'select_a' => $select_a,
      'select_b' => $select_b,
      'select_c' => $select_c,
    ]);
    
    $form['#tree'] = TRUE;

    // all selects will be children of this node, which will be replaced in its entirety by selectCallback()
    $form['select_wrapper'] = [
      '#type' => 'fieldset',
      '#title' => 'Multiple Select',
      '#prefix' => '<div id="select-wrapper">',
      '#suffix' => '</div>',
    ];
    
    // the first select will be shown no matter what
    $form['select_wrapper']['select_a'] = [
      '#type' => 'select',
      '#title' => 'Element A',
      '#empty_option' => '-- select an element --',
      '#empty_value' => 'none',
      '#options' => [
        "a-one" => 'a-one',
        "a-two" => 'a-two',
        "a-three" => 'a-three',
      ],
      // we only need to bind the callback to return a specific chunk of the form
      '#ajax' => [
        'callback' => '::SelectCallback',
        'wrapper' => 'select-wrapper', // must match the HTML ID of the DOM element to be replaced, not the key in the $form array
      ],
    ];
    
    if($select_a !== 'none') {
      
      // the second select gets added depending on the first selection
      $form['select_wrapper']['select_b'] = [
        '#type' => 'select',
        '#title' => 'Element B',
        '#empty_option' => '-- select an element --',
        '#empty_value' => 'none',
        '#options' => [
          "b-one" => "$select_a b-one", 
          "b-two" => "$select_a b-two", 
          "b-three" => "$select_a b-three",
        ],
        // identical callback as that of select_a
        '#ajax' => [
          'callback' => '::SelectCallback',
          'wrapper' => 'select-wrapper',
        ],
      ];
      
      if($select_b !== 'none') {
        
        // the third select gets added depending on both preceding selections
        $form['select_wrapper']['select_c'] = [
          '#type' => 'select',
          '#title' => 'Element C',
          '#empty_option' => '-- select an element --',
          '#empty_value' => 'none',
          '#options' => [
            "c-one" => "$select_a $select_b c-one", 
            "c-two" => "$select_a $select_b c-two", 
            "c-three" => "$select_a $select_b c-three",
          ],
          // no callback here
        ];
      }
    }
    
    $form['actions'] = array(
      '#type' => 'actions',
    );
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => 'Submit',
    ];

    $form_state->setCached(FALSE);
    return $form;
  }

  // getFormId() gets called by default
  public function getFormId() {
    self::dump('getFormId');
    return 'entajax_selects';
  }
   
  // selectCallback() is a custom method, must be bound manually
  public function selectCallback(array &$form, FormStateInterface $form_state) {
    self::dump('selectCallback');
    return $form['select_wrapper'];
  }

  // validateForm() gets called by default
  public function validateForm(array &$form, FormStateInterface $form_state) {
    self::dump('validateForm');
  }
  
  // submitForm() gets called by default
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $select_a = $form_state->getValue(['select_wrapper', 'select_a'], 'none');
    $select_b = $form_state->getValue(['select_wrapper', 'select_b'], 'none');
    $select_c = $form_state->getValue(['select_wrapper', 'select_c'], 'none');
    
    self::dump('submitForm', [
      'select_a' => $select_a,
      'select_b' => $select_b,
      'select_c' => $select_c,
    ]);
  }

  // helper function to show and inspect the call flow, pointless otherwise
  private static function dump(string $label, $var = null) {
    $time = microtime(true);
    $format = 'Y/m/d - H:i:s';
    $micro = $time - floor($time);
    $micro = substr($micro, 1, 7);
    $stamp = sprintf('%s%s', date($format), $micro);
    $label = htmlspecialchars($label);
    $message = \Drupal\Core\Render\Markup::create(
      "<pre><small>$stamp</small><br><strong>$label</strong> => "
      . htmlspecialchars(print_r($var, true))
      . '</pre>'
    );
    drupal_set_message($message); 
  }
  
}
