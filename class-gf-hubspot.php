<?php

GFForms::include_feed_addon_framework();

class GFHubSpot extends GFFeedAddOn {

  protected $_version = '1.0';
  protected $_min_gravityforms_version = '1.8.17';
  protected $_slug = 'gravityformshubspot';
  protected $_path = 'gravityformshubspot/gravityformshubspot.php';
  protected $_full_path = __FILE__;
  protected $_title = 'Gravity Forms Hub spot addon';
  protected $_short_title = 'HubSpot';

  private static $hubspot;

  private static $_instance = null;

  /**
   * Get an instance of this class.
   *
   * @return GFSimpleFeedAddOn
   */
  public static function get_instance() {
    if ( self::$_instance == null ) {
      self::$_instance = new GFHubSpot();
    }

    return self::$_instance;
  }

  /**
   * Plugin starting point. Handles hooks, loading of language files and PayPal delayed payment support.
   */
  public function init() {

    parent::init();

    $this->add_delayed_payment_support(
      array(
        'option_label' => esc_html__( 'Subscribe contact to service x only when payment is received.', 'simplefeedaddon' )
      )
    );

  }

  /**
   * Process the feed.
   *
   * @param array $feed The feed object to be processed.
   * @param array $entry The entry object currently being processed.
   * @param array $form The form object currently being processed.
   *
   * @return bool|void
   */
  public function process_feed( $feed, $entry, $form ) {

    $feedName  = $feed['meta']['feedName'];

    // Retrieve the name => value pairs for all fields mapped in the 'mappedFields' field map.
    $field_map = $this->get_field_map_fields( $feed, 'mappedFields' );

    // Loop through the fields from the field map setting building an array of values to be passed to the third-party service.
    $merge_vars = array();
    foreach ( $field_map as $name => $field_id ) {

      // Get the field value for the specified field id
      $merge_vars[ $name ] = $this->get_field_value( $form, $entry, $field_id );

    }

    // Send the values to the third-party service.
  }

  /**
   * Creates a custom page for this add-on.
   */
  public function plugin_page() {
    echo 'This page appears in the Forms menu';
  }

  /**
   * Configures the settings which should be rendered on the add-on settings tab.
   *
   * @return array
   */
  public function plugin_settings_fields() {
    return array(
      array(
        'title'       => __( 'HubSpot Account Information', 'gravityformshubspot' ),
        'description' => sprintf(
          __( "Hubspot is a CRM system it's probably rate gud...." ),
          '<a href="http://www.hubspot.com/" target="_blank">', '</a>'
        ),
        'fields'      => array(
          array(
            'name'              => 'apiKey',
            'label'             => __( 'HubSpot API Key', 'gravityformshubspot' ),
            'type'              => 'text',
            'class'             => 'medium',
            'feedback_callback' => array( $this, 'is_valid_api_key' )
          ),
        )
      ),
    );
  }

  public function is_valid_api_key( $apikey ) {
    //@todo: Write validation function.
    return true;
  }

  /**
   * Configures the settings which should be rendered on the feed edit page in the Form Settings > Simple Feed Add-On area.
   *
   * @return array
   */
  public function feed_settings_fields() {

    return array(
      array(
        'title'       => __( 'MailChimp Feed Settings', 'gravityformsmailchimp' ),
        'description' => '',
        'fields'      => array(
          array(
            'name'     => 'feedName',
            'label'    => __( 'Name', 'gravityformshubspot' ),
            'type'     => 'text',
            'required' => true,
            'class'    => 'medium',
            'tooltip'  => '<h6>' . __( 'Name', 'gravityformshubspot' ) . '</h6>' . __( 'Enter a feed name to uniquely identify this setup.', 'gravityformshubspot' )
          ),
          array(
            'name'     => 'hubspotForm',
            'label'    => __( 'HubSpot Form', 'gravityformshubspot' ),
            'type'     => 'hubspot_form',
            'required' => true,
            'tooltip'  => '<h6>' . __( 'MailChimp List', 'gravityformshubspot' ) . '</h6>' . __( 'Select the HubSpot list you would like to add your contacts to.', 'gravityformshubspot' ),
          ),
        )
      ),
      array(
        'title'       => '',
        'description' => '',
        'dependency'  => 'hubspotForm',
        'fields'      => array(
          array(
            'name'      => 'mappedFields',
            'label'     => __( 'Map Fields', 'gravityformshubspot' ),
            'type'      => 'field_map',
            'field_map' => $this->merge_vars_field_map(),
            'tooltip'   => '<h6>' . __( 'Map Fields', 'gravityformsmailchimp' ) . '</h6>' . __( 'Associate your MailChimp merge variables to the appropriate Gravity Form fields by selecting.', 'gravityformsmailchimp' ),
          ),
          array( 'type' => 'save' ),
        )
      ),
    );
  }

  /**
   * Configures which columns should be displayed on the feed list page.
   *
   * @return array
   */
  public function feed_list_columns() {
    return array(
      'feedName'  => esc_html__( 'Name', 'gravityformshubspot' ),
      'hubspot_form' => esc_html__('HubSpot Form', 'gravityformshubspot' ),
    );
  }

  public function get_column_value_hubspot_form_name( $feed ) {
    return $this->get_form_name( $feed['meta']['hubspotForm'] );
  }

  public function settings_hubspot_form( $field, $setting_value = '', $echo = true ) {

    $hubspot  = $this->get_api();
    $html = '';

    // Getting all forms.
    $response = $hubspot->forms()->all();
    $forms = $response->data;

    $options = array(
      array(
        'label' => __( 'Select a Hubspot Form', 'gravityformshubspot' ),
        'value' => ''
      )
    );

    if ( empty( $forms ) ) {
      echo __( 'Could not load HubSpot forms. <br/>Error: ', 'gravityformshubspot' );
    }
    else{
      foreach ( $forms as $key => $value ) {
        $options[] = array(
          'label' => esc_html( $value->name ),
          'value' => esc_attr( $value->guid )
        );
      }
    }

    $field['type']     = 'select';
    $field['choices']  = $options;
    $field['onchange'] = 'jQuery(this).parents("form").submit();';

    $html = $this->settings_select( $field, $setting_value, false );

    if ( $echo ) {
      echo $html;
    }

    return $html;

  }

  /**
   * Prevent feeds being listed or created if an api key isn't valid.
   *
   * @return bool
   */
  public function can_create_feed() {

    // Get the plugin settings.
    $settings = $this->get_plugin_settings();

    // Access a specific setting e.g. an api key
    $key = rgar( $settings, 'apiKey' );

    return true;
  }

  /**
   * Get an instance of the hubspot API.
   * @return [type] [description]
   */
  private function get_api() {

    if ( self::$hubspot ) {
      return self::$hubspot;
    }

    $settings = $this->get_plugin_settings();
    $hubspot = null;

    if ( ! empty( $settings['apiKey'] ) ) {

      $apikey = $settings['apiKey'];
      require __DIR__ . '/vendor/autoload.php';
      $hubspot = SevenShores\Hubspot\Factory::create( $apikey );
      $forms = $hubspot->forms()->all();
    } else {
      $this->log_debug( __METHOD__ . '(): API credentials not set.' );
      return null;
    }

    self::$hubspot = $hubspot;
    return self::$hubspot;
  }

  public function merge_vars_field_map() {

    $hubspot = $this->get_api();
    $form_id = $this->get_setting( 'hubspotForm' );
    $form_fields = $hubspot->forms()->getFields( $form_id )->data;
    $field_map = array();

    // if ( ! empty( $list_id ) ) {
    //   try {
    //     $params = array(
    //       'id' => array( $list_id ),
    //     );

    //     $lists = $api->call( 'lists/merge-vars', $params );
    //   } catch ( Exception $e ) {
    //     $this->log_error( __METHOD__ . '(): ' . $e->getCode() . ' - ' . $e->getMessage() );

    //     return $field_map;
    //   }

    //   if ( empty( $lists['data'] ) ) {
    //     $this->log_error( __METHOD__ . '(): Unable to retrieve list due to ' . $lists['errors'][0]['code'] . ' - ' . $lists['errors'][0]['error'] );

    //     return $field_map;
    //   }
    //   $list       = $lists['data'][0];
    //   $merge_vars = $list['merge_vars'];

    foreach ( $form_fields as $field ) {
      $field_map[] = array(
        'name'     => $field->name,
        'label'    => $field->label,
        'required' => $field->required,
      );
    };

    return $field_map;
  }

  private function get_form_name( $form_guid ) {
    global $_forms;

    if ( ! isset( $_lists ) ) {
      $api = $this->get_api();
      if ( ! is_object( $api ) ) {
        $this->log_error( __METHOD__ . '(): Failed to set up the API.' );

        return '';
      }
      try {
        $params = array(
          'start' => 0,
          'limit' => 100,
        );
        $_lists = $api->call( 'lists/list', $params );
      } catch ( Exception $e ) {
        $this->log_debug( __METHOD__ . '(): Could not load MailChimp contact lists. Error ' . $e->getCode() . ' - ' . $e->getMessage() );

        return '';
      }
    }

    $list_name_array = wp_filter_object_list( $_lists['data'], array( 'id' => $list_id ), 'and', 'name' );
    if ( $list_name_array ) {
      $list_names = array_values( $list_name_array );
      $list_name  = $list_names[0];
    } else {
      $list_name = $list_id . ' (' . __( 'Form not found in HubSpot', 'gravityformshubspot' ) . ')';
    }

    return $list_name;
  }

}


