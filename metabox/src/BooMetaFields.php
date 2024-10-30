<?php

namespace BooMeta;

if ( !defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

class BooMetaFields
{
    /**
     * Define metabox core
     *
     * @param array $fields
     * @param [type] $post
     */
    public function __construct( private $fields = [], private $post = null )
    {
    }

    /**
     * Add meta boxes.
     *
     * @return void
     */
    public function add_meta_boxes()
    {
        foreach ( $this->fields as $metabox ) {

            $metabox_id = $this->post . '_metabox_' . $metabox[ 'slug' ];
            $metabox_label = $metabox[ 'label' ];
            $metabox_callback = array (
                $this,
                'create_ctp_custom_metadata'
            );
            $metabox_screen = $this->post;
            $metabox_content = 'normal';
            $metabox_priority = 'default';
            $metabox_callback_args = array (
                $metabox[ 'metadata' ],
                $this->post
            );

            add_meta_box( $metabox_id, $metabox_label, $metabox_callback, $metabox_screen, $metabox_content, $metabox_priority, $metabox_callback_args );
        }
    }

    /**
     * Create View custom meta boxes
     *
     * @param [type] $post
     * @param [type] $data
     * @return void
     */
    public function create_ctp_custom_metadata( $post, $data )
    {
        $metabox = $this->fields;
        $metadata = $data[ 'args' ][ 0 ];
        $post_type_slug = $data[ 'args' ][ 1 ];

        $html = '<ul class="com-tab-group">';
        foreach ( $metabox[ 0 ][ 'tabs' ] as $key => $element ) {
            $html .= '<li class="' . ( $key === 0 ? 'active' : '' ) . ' ' . $element[ 'key' ] . '">
                <a href="" class="com-tab-button" data-endpoint="0" data-key="' . $element[ 'key' ] . '">' . esc_attr( $element[ 'label' ] ) . '</a>
                </li>';
        }

        $html .= '</ul>';

        foreach ( $metadata as $metadatum ) {

            $html .= '<div class="metadata-wrap ' . $metadatum[ 'tab_class' ] . ' ' . ( $metadatum[ 'tab_class' ] ===  $metabox[ 0 ][ 'tabs' ][ 0 ][ 'key' ] ? 'active' : 'hidden' ) . '">';

            $metadatum_type = array_key_exists( 'type', $metadatum ) ? $metadatum[ 'type' ] : 'text';
            $metadatum_attr = array_key_exists( 'attr', $metadatum ) ? $metadatum[ 'attr' ] : '';
            $metadatum_label = array_key_exists( 'label', $metadatum ) ? $metadatum[ 'label' ] : '';
            $metadatum_desc = array_key_exists( 'desc', $metadatum ) ? $metadatum[ 'desc' ] : '';
            $metadatum_slug = array_key_exists( 'slug', $metadatum ) ? $metadatum[ 'slug' ] : '';
            $metadatum_default = array_key_exists( 'default', $metadatum ) ? $metadatum[ 'default' ] : '';
            $metadatum_options = array_key_exists( 'options', $metadatum ) ? $metadatum[ 'options' ] : '';
            $metadatum_tab_class = array_key_exists( 'tab_class', $metadatum ) ? $metadatum[ 'tab_class' ] : '';
            $metadatum_fields = array_key_exists( 'fields', $metadatum ) ? $metadatum[ 'fields' ] : '';
            $metadatum_id = $post_type_slug . '_metadata_' . $metadatum_slug;
            $metadatum_value = get_post_meta( $post->ID, $metadatum_id, true );
            $metadatum_value = $metadatum_value ? $metadatum_value : $metadatum_default;

            register_meta( $post_type_slug, $metadatum_id, array (
                'single' => true,
                'show_in_rest' => true
            ) );

            switch ( $metadatum_type ) {

                case 'hidden':

                    $html .= '<input type="hidden" name="' . $metadatum_id . '" id="' . $metadatum_id . '" value="' . $metadatum_value . '" class="widefat" />';

                    break;

                case 'number':
                    $html .= '<p class="post-attributes-label-wrapper"><label class="post-attributes-label" for="' . $metadatum_id . '">' . $metadatum_label . ( isset( $metadatum[ 'required' ] ) ? ' *' : '' ) . '</label></p>';

                    $html .= '<div class="metadata-desc">' . $metadatum_desc . '</div>';

                    $html .= '<input ' . ( isset( $metadatum[ 'required' ] ) ? 'required' : '' ) . ' type="number" min="' . $metadatum[ 'min' ] . '" max="' . $metadatum[ 'max' ] . '"  name="' . $metadatum_id . '" id="' . $metadatum_id . '" value="' . $metadatum_value . '" data-tabclass="' . $metadatum_tab_class . '" class="widefat" />';

                    break;

                case 'json':

                    $html .= '<p class="post-attributes-label-wrapper"><label class="post-attributes-label" for="' . $metadatum_id . '">' . $metadatum_label . ( isset( $metadatum[ 'required' ] ) ? ' *' : '' ) . '</label></p>';

                    $html .= '<div class="metadata-desc">' . $metadatum_desc . '</div>';

                    $html .= '<textarea rows="10" cols="20" ' . $metadatum_attr . ( isset( $metadatum[ 'required' ] ) ? ' required' : '' ) . ' name="' . $metadatum_id . '" id="' . $metadatum_id . '" data-tabclass="' . $metadatum_tab_class . '" class="widefat">' . $metadatum_value . '</textarea>';

                    break;
                case 'select':

                    $html .= '<p class="post-attributes-label-wrapper"><label class="post-attributes-label" for="' . $metadatum_id . '">' . $metadatum_label . ( isset( $metadatum[ 'required' ] ) ? ' *' : '' ) . '</label></p>';

                    $html .= '<div class="metadata-desc">' . $metadatum_desc . '</div>';

                    $html .= '<select ' . ( isset( $metadatum[ 'required' ] ) ? 'required' : '' ) . ' name="' . $metadatum_id . '" id="' . $metadatum_id . '" data-tabclass="' . $metadatum_tab_class . '" class="widefat">';

                    foreach ( $metadatum_options as $metadatum_option_label => $metadatum_option_value ) {

                        $html .= '<option' . ( $metadatum_option_value == $metadatum_value ? ' selected="selected"' : '' ) . ' value="' . $metadatum_option_value . '">' . $metadatum_option_label . '</option>';
                    }

                    $html .= '</select>';

                    break;

                case 'textarea':

                    $html .= '<p class="post-attributes-label-wrapper"><label class="post-attributes-label" for="' . $metadatum_id . '">' . $metadatum_label . ( isset( $metadatum[ 'required' ] ) ? ' *' : '' ) . '</label></p>';

                    $html .= '<div class="metadata-desc">' . $metadatum_desc . '</div>';

                    $html .= '<textarea ' . $metadatum_attr . ( isset( $metadatum[ 'required' ] ) ? ' required' : '' ) . ' name="' . $metadatum_id . '" id="' . $metadatum_id . '" data-tabclass="' . $metadatum_tab_class . '" class="widefat">' . $metadatum_value . '</textarea>';

                    break;

                case 'toggle':

                    $html .= '<p class="post-attributes-label-wrapper"><label class="post-attributes-label">' . $metadatum_label . ( isset( $metadatum[ 'required' ] ) ? ' *' : '' ) . '</label></p>';

                    $html .= '<div class="metadata-desc">' . $metadatum_desc . '</div>';

                    $html .= '<toggle>';

                    foreach ( $metadatum_options as $key => $metadatum_option ) {
                        $html .= '<label><input data-tabclass="' . $metadatum_tab_class . '" ' . ( isset( $metadatum[ 'required' ] ) ? 'required' : '' ) . ' type="radio" name="' . $metadatum_id . '"' . ( $metadatum_option == $metadatum_value ? ' checked="checked"' : '' ) . ' value="' . $metadatum_option . '" /><div>' . $key . '</div></label>';
                    }

                    $html .= '</toggle>';

                    break;

                case 'color':

                    $html .= '<p class="post-attributes-label-wrapper"><label class="post-attributes-label" for="' . $metadatum_id . '">' . $metadatum_label . ( isset( $metadatum[ 'required' ] ) ? ' *' : '' ) . '</label></p>';

                    $html .= '<div class="metadata-desc">' . $metadatum_desc . '</div>';

                    $html .= '<input type="text" name="' . $metadatum_id . '" id="' . $metadatum_id . '" value="' . $metadatum_value . '" data-tabclass="' . $metadatum_tab_class . '" class="widefat color_field" />';

                    break;

                case 'repeater':

                    $html .= '<p class="post-attributes-label-wrapper"><label class="post-attributes-label" for="' . $metadatum_id . '">' . $metadatum_label . ( isset( $metadatum[ 'required' ] ) ? ' *' : '' ) . '</label></p>';

                    $html .= '<div class="metadata-desc">' . $metadatum_desc . '</div>';

                    $html .= '<table id="repeatable-fieldset-one" width="100%"><thead><tr>';

                    foreach ( $metadatum_fields as $field ) {
                        $html .= '<th width="' . 100 / count( $metadatum_fields ) . '%">' . esc_attr( $field[ 'label' ] ) . '</th> ';
                    }
                    $html .= '</tr></thead><tbody>';
                    $field_name = 'repeater_' . $metadatum[ 'slug' ] . '_field';
                    $serialize_data = get_post_meta( $post->ID, $metadatum_id, true );
                    $repeatable_fields = [];

                    for ( $meta_i = 0; $meta_i < count( $metadatum[ 'fields' ] ); $meta_i++ ) {

                        if ( $serialize_data ) {
                            $data = unserialize( $serialize_data );

                            if ( is_array( $data ) ) {

                                foreach ( $data as $key => $value ) {
                                    $repeatable_fields[ $key ] = $value;
                                }
                            } else {
                                $repeatable_fields[] = stripslashes( wp_strip_all_tags( $data ) );
                            }
                        }
                    }

                    if ( $repeatable_fields ) :

                        foreach ( $repeatable_fields as $field ) {
                            $html .= '<tr>';

                            for ( $i = 0; $i < count( $field ); $i++ ) {
                                $field_id = $field_name . '_' . $metadatum[ 'fields' ][ $i ][ 'slug' ];

                                if ( $field[ $field_id ] ) {
                                    if ( $field[ $field_id ][ 'type' ] === 'text' ) {
                                        $html .= '<td><input type="text" class="widefat" name="' . $field_name . '_' . $metadatum[ 'fields' ][ $i ][ 'slug' ] . '[]" value="' . $field[ $field_id ][ 'data' ] . '" /></td>';
                                    } elseif ( $field[ $field_id ][ 'type' ] === 'select' ) {
                                        $html .= '<td><select name="' . $field_name . '_' . $metadatum[ 'fields' ][ $i ][ 'slug' ] . '[]" class="widefat">';

                                        foreach ( $metadatum[ 'fields' ][ $i ][ 'options' ] as $option_label => $option_value ) {

                                            $html .= '<option' . ( $option_value === $field[ $field_id ][ 'data' ] ? ' selected="selected"' : '' ) . ' value="' . $option_value . '">' . $option_label . '</option>';
                                        }

                                        $html .= '</select></td>';
                                    }
                                }
                            }
                            $html .= '<td><a class="button remove-row" href="#">' . esc_attr__( 'Remove', 'casa-courses' ) . '</a></td>
                        </tr>';
                        }

                    else :
                        // show a blank one   
                        $html .= '<tr>' . self::repeater_field_generate_helper( $metadatum_fields, $field_name, $metadatum_tab_class ) . '</tr>';
                    endif;

                    $html .= '<!-- empty hidden one for jQuery --><tr class="empty-row screen-reader-text">';
                    $html .= self::repeater_field_generate_helper( $metadatum_fields, $field_name, $metadatum_tab_class );
                    $html .= '<td><a class="button remove-row" href="#">' . esc_attr__( 'Remove', 'casa-courses' ) . '</a></td></tr>
                    </tbody>
                    </table>';

                    if ( $metadatum_attr !== 'readonly' ) {
                        $html .= '<p><a class="button add-row" href="#">' . esc_attr__( 'Add another', 'casa-courses' ) . '</a></p>';
                    }

                    break;

                default:

                    $html .= '<p class="post-attributes-label-wrapper"><label class="post-attributes-label" for="' . $metadatum_id . '">' . $metadatum_label . ( isset( $metadatum[ 'required' ] ) ? ' *' : '' ) . '</label></p>';

                    $html .= '<div class="metadata-desc">' . $metadatum_desc . '</div>';

                    $html .= '<input ' . $metadatum_attr . ( isset( $metadatum[ 'required' ] ) ? ' required' : '' ) . ' type="' . $metadatum_type . '" name="' . $metadatum_id . '" id="' . $metadatum_id . '" value="' . $metadatum_value . '" data-tabclass="' . $metadatum_tab_class . '" class="widefat" />';

                    break;
            }

            $html .= '</div>';
        }

        $allowed_html = [
            'div' => [
                'class' => true,
            ],
            'p' => [
                'class' => true,
            ],
            'label' => [
                'class' => true,
                'for' => true,
            ],
            'input' => [
                'type' => true,
                'name' => true,
                'id' => true,
                'value' => true,
                'class' => true,
                'min' => true,
                'max' => true,
                'data-tabclass' => true,
                'required' => true,
                'readonly' => true
            ],
            'textarea' => [
                'rows' => true,
                'cols' => true,
                'name' => true,
                'id' => true,
                'class' => true,
                'data-tabclass' => true,
                'required' => true,
                'readonly' => true
            ],
            'select' => [
                'name' => true,
                'id' => true,
                'class' => true,
                'data-tabclass' => true,
                'required' => true,
            ],
            'option' => [
                'value' => true,
                'selected' => true,
            ],
            'toggle' => [],
            'table' => [
                'id' => true,
                'width' => true,
            ],
            'thead' => [],
            'tr' => [],
            'th' => [
                'width' => true,
            ],
            'tbody' => [],
            'td' => [],
            'a' => [
                'class' => true,
                'href' => true,
                "data-endpoint" => true,
                "data-key" => true
            ],
            'strong' => [],
            'ul' => [
                'class' => true,
            ],
            'li' => [
                'class' => true,
            ]
        ];

        echo wp_kses( $html, $allowed_html ) . '<input type="hidden" name="custommeta_noncename" id="custommeta_noncename" value="' . esc_attr( wp_create_nonce( basename( __FILE__ ) ) ) . '" />';
    }

    public static function repeater_field_generate_helper( $metadatum_fields, $field_name, $tab_class = '' ): string
    {
        $html = '';
        foreach ( $metadatum_fields as $field ) {
            if ( $field[ 'type' ] === 'text' ) {
                $html .= '<td><input ' . ( isset( $field[ 'attr' ] ) && $field[ 'attr' ] ) . ' type="text" class="widefat" data-tabclass="' . $tab_class . '" name="' . $field_name . '_' . $field[ 'slug' ] . '[]" /></td>';
            } elseif ( $field[ 'type' ] === 'select' ) {
                $html .= '<td><select name="' . $field_name . '_' . $field[ 'slug' ] . '[]" class="widefat" data-tabclass="' . $tab_class . '">';

                foreach ( $field[ 'options' ] as $option_label => $option_value ) {
                    $html .= '<option' . ( $option_label == array_key_first( $field[ 'options' ] ) ? ' selected="selected"' : '' ) . ' value="' . $option_value . '">' . $option_label . '</option>';
                }
                $html .= '</select></td>';
            }
        }

        $allowed_html = [
            'td' => [],
            'input' => [
                'type' => true,
                'class' => true,
                'data-tabclass' => true,
                'name' => true,
            ],
            'select' => [
                'name' => true,
                'class' => true,
                'data-tabclass' => true,
            ],
            'option' => [
                'value' => true,
                'selected' => true,
            ],
        ];

        return wp_kses( $html, $allowed_html );
    }
}
