<?php
/**
 * QS_Section
 *  This Class is responsible for handling metadata ( ACF ) in Wordpress for Sections.
 *  With this Class you can - Get data.
 *
 * @category    Wordpress
 * @author      NivNoiman
 * Text-Domain: qs_section
 */
class QS_Section {
    /* ###### Properties ###### */
    public $sectionData = array(); // returned user data
    protected static $metaStructure; // the structure of the fields in wordpress database
    /* ###### Magic ###### */
    /**
     * __construct
     * by declared class with values ( name of the section [ prefix ] ) - the first action will be the insert all the data to $sectionData.
     * @param [string] $name_id [ prefix - the unique name prefix for the fields ]
     */
    function __construct( $name_id ){
        $this->init( $name_id );
    }
    public function __set( $key , $vlue ){
        self::$metaStructure[ $key ] = $vlue;
        $this->init();
    }
    /**
     * __get
     * get params from class
     * @param [string] $string [ the field we want to get his value ]
     */
    public function __get( $string ){
        if( array_key_exists( $string , $this->sectionData ) ){
            if( is_array( $this->sectionData[ $string ] ) ){
                if( $string == 'background')
                    return $this->generate_BackgroundStyle( $this->sectionData[ $string ]['image'] , $this->sectionData[ $string ]['color'] , false );
            } else
                return $this->sectionData[ $string ];
        }
    }
    /* ###### Functions ###### */
    /**
     * init
     * initialize the class with all the values
     * @param [string] $name_id [ prefix - the unique name prefix for the fields ]
     */
    protected function init( $name_id = NULL){
        if( !empty( $name_id ) ){
            self::meta_name_structure( $name_id );
        }
        foreach ( self::$metaStructure as $key => $value ) {
            if( is_array( $value ) ){
                foreach ( $value as $k => $v )
                    $this->sectionData[ $key ][ $k ] = self::getField( $v );
            } else
                $this->sectionData[ $key ] = self::getField( $value );
        }
    }
    /**
     * prefixIt
     * this function returned the $name_id as prefix
     * @param [string] $name_id [ prefix - the unique name prefix for the fields ]
     */
    protected function prefixIt( $name_id ){
        if( substr( $name_id, -1 ) == '_' )
            $prefix = $name_id;
        else
            $prefix = $name_id . '_';
        return $prefix;
    }
    /**
     * meta_name_structure
     * create the structure of the metadata fields
     * @param [string] $name_id [ prefix - the unique name prefix for the fields ]
     */
    protected function meta_name_structure( $name_id ){
        $prefix = self::prefixIt( $name_id );
        self::$metaStructure = array(
            'background' =>  array(
                'image' => $prefix . 'background_image',
                'color' => $prefix . 'background_color'
            ),
            'title'   => $prefix . 'title',
            'content' => $prefix . 'content'
        );
    }
    /**
     * getField
     * get post metadata
     * @param [string] $name_id [ prefix - the unique name prefix for the fields ]
     * @param [numeric] $post_id [ the post_id we want to get the data from . by default the current POST ID ]
     */
    protected function getField( $field_name , $post_id = NULL ){
        $query_object = get_queried_object();
        $post_id = ( !empty( $post_id ) && is_numeric( $post_id ) ) ? $post_id : !empty( $query_object->ID ) ? $query_object->ID : 'options';
        if( function_exists('get_field') ){
            return get_field( $field_name , $post_id );
        } else
            echo __( 'YOU NEED ACF PLUGIN TO BE ENABLE FOR THIS CLASS' , 'qs_section');
    }
    /**
     * generate_BackgroundStyle
     * generate css style for html's container tag
     * @param [array] $image [ wordpress image array data ]
     * @param [string] $color [ color hex string ]
     * @param [bool] $height [ if true - get the height of the image and style it ]
     * @return style css for inline html tag
     */
    public function generate_BackgroundStyle( $image = array() , $color = '' , $height = false){
        $inlineStyle = '';
        if( !empty( $image ) || !empty( $color ) ){
            if( !empty( $image['sizes']['section1'] ) )
                $image['url'] = $image['sizes']['section1'];

            if( !empty( $image['url'] ) )
                $inlineStyle .= ' background-image: url('.$image['url'].');';
            if( !empty( $image['height'] ) && $height )
                $inlineStyle .= ' height: '.$image['height'].'px;';
            if( !empty( $color ) )
                $inlineStyle .= ' background-color: '.$color.';';
                return $inlineStyle;
        }
    }
    /**
     * the_title
     * Print the title with all html tags
     * @param [array] $args [ tags -> wrapper tags for the title | title -> overwrite title text ]
     * @return the title with html wrapper.
     */
    public function the_title( $args = array( 'tag' => 'h1') ){
        global $post;
        $title       = !empty( $this->title ) ? $this->title : get_post_field( 'post_title', $post->ID );

        $title_html  = '<div class="page_title">';
        $title_html .= !empty( $args['tag'] ) ? '<'.$args['tag'].'>' : '';
        $title_html .= !empty( $args['title'] ) ? $args['title'] : $title;
        $title_html .= !empty( $args['tag'] ) ? '</'.$args['tag'].'>' : '';
        $title_html .= '</div>';

        $title_html = apply_filters( 'qs_section_the_title', $title_html );

        echo $title_html;
    }
    /**
     * the_content
     * Print the page content with all html tags
     * @param [array] $args [ tags -> wrapper tags for the content]
     * @return the content with html wrapper.
     */
    public function the_content( $args = array( 'tag' => 'div class="wp_content"') ){
        global $post;
        $content       = !empty( $this->content ) ? $this->content : get_post_field( 'post_content', $post->ID );

        $content_html  = '<div class="page_content">';
        $content_html .= !empty( $args['tag'] ) ? '<'.$args['tag'].'>' : '';
        $content_html .= wpautop( $content );
        $content_html .= !empty( $args['tag'] ) ? '</'.strstr( $args['tag'], ' ', true ).'>' : '';
        $content_html .= '</div>';

        $content_html = apply_filters( 'qs_section_the_content', $content_html );

        echo $content_html;
    }
}
?>
