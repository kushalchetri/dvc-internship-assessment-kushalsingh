<?php
/**
 * Plugin Name: Testimonials Plugin
 * Description: A lightweight custom post type plugin to manage and display testimonials via shortcode.
 * Version: 1.0.0
 * Author: Nikit
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Testimonials_Plugin {
    public function __construct() {
        add_action( 'init', [ $this, 'register_cpt' ] );
        add_action( 'add_meta_boxes', [ $this, 'add_metaboxes' ] );
        add_action( 'save_post', [ $this, 'save_meta' ] );
        add_shortcode( 'testimonials', [ $this, 'render_shortcode' ] );
    }

    public function register_cpt() {
        register_post_type( 'testimonial', [
            'labels'              => [
                'name'               => 'Testimonials',
                'singular_name'      => 'Testimonial',
                'add_new'            => 'Add New',
                'add_new_item'       => 'Add New Testimonial',
                'edit_item'          => 'Edit Testimonial',
                'new_item'           => 'New Testimonial',
                'view_item'          => 'View Testimonial',
                'search_items'       => 'Search Testimonials',
                'not_found'          => 'No testimonials found',
                'not_found_in_trash' => 'No testimonials found in Trash',
            ],
            'public'              => true,
            'supports'            => [ 'title', 'editor', 'thumbnail' ],
            'menu_icon'           => 'dashicons-testimonial',
            'show_in_rest'        => true,
            'exclude_from_search' => true,
        ]);
    }

    public function add_metaboxes() {
        add_meta_box( 'testimonial_meta', 'Client Details', [ $this, 'render_metabox' ], 'testimonial', 'normal', 'high' );
    }

    public function render_metabox( $post ) {
        wp_nonce_field( 'save_testimonial_meta', 'testimonial_nonce' );

        $client_name = get_post_meta( $post->ID, '_testimonial_client_name', true );
        $position    = get_post_meta( $post->ID, '_testimonial_position', true );
        $company     = get_post_meta( $post->ID, '_testimonial_company', true );
        $rating      = get_post_meta( $post->ID, '_testimonial_rating', true ) ?: '5';

        ?>
        <table class="form-table">
            <tr>
                <th><label for="testimonial_client_name">Client Name *</label></th>
                <td><input type="text" name="testimonial_client_name" value="<?php echo esc_attr( $client_name ); ?>" class="regular-text" required /></td>
            </tr>
            <tr>
                <th><label for="testimonial_position">Position</label></th>
                <td><input type="text" name="testimonial_position" value="<?php echo esc_attr( $position ); ?>" class="regular-text" /></td>
            </tr>
            <tr>
                <th><label for="testimonial_company">Company</label></th>
                <td><input type="text" name="testimonial_company" value="<?php echo esc_attr( $company ); ?>" class="regular-text" /></td>
            </tr>
            <tr>
                <th><label for="testimonial_rating">Rating (1-5)</label></th>
                <td>
                    <select name="testimonial_rating">
                        <?php for ( $i = 1; $i <= 5; $i++ ) : ?>
                            <option value="<?php echo $i; ?>" <?php selected( $rating, $i ); ?>><?php echo $i; ?> Stars</option>
                        <?php endfor; ?>
                    </select>
                </td>
            </tr>
        </table>
        <?php
    }

    public function save_meta( $post_id ) {
        if ( ! isset( $_POST['testimonial_nonce'] ) || ! wp_verify_nonce( $_POST['testimonial_nonce'], 'save_testimonial_meta' ) ) {
            return;
        }
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
        if ( ! current_user_can( 'edit_post', $post_id ) ) return;

        $fields = [
            'testimonial_client_name' => '_testimonial_client_name',
            'testimonial_position'    => '_testimonial_position',
            'testimonial_company'     => '_testimonial_company',
            'testimonial_rating'      => '_testimonial_rating',
        ];

        foreach ( $fields as $input => $meta_key ) {
            if ( isset( $_POST[ $input ] ) ) {
                update_post_meta( $post_id, $meta_key, sanitize_text_field( wp_unslash( $_POST[ $input ] ) ) );
            }
        }
    }

    public function render_shortcode( $atts ) {
        $atts = shortcode_atts( [ 'count' => -1 ], $atts, 'testimonials' );
        $query = new WP_Query( [
            'post_type'      => 'testimonial',
            'posts_per_page' => intval( $atts['count'] ),
        ] );

        if ( ! $query->have_posts() ) {
            return '<p>No testimonials available.</p>';
        }

        ob_start();
        $slider_id = 'ts-slider-' . wp_rand( 100, 999 );
        ?>
        <style>
            .ts-container { max-width: 800px; margin: 0 auto; overflow: hidden; background: #fff; padding: 40px 20px; border-radius: 12px; box-shadow: 0 4px 10px rgba(0,0,0,0.05); position: relative; font-family: sans-serif; }
            .ts-wrapper { display: flex; transition: transform 0.4s ease; }
            .ts-slide { min-width: 100%; box-sizing: border-box; text-align: center; padding: 0 20px; opacity: 0; transition: opacity 0.4s; }
            .ts-slide.active { opacity: 1; }
            .ts-img { width: 80px; height: 80px; border-radius: 50%; object-fit: cover; margin-bottom: 15px; }
            .ts-stars { color: #f59e0b; margin-bottom: 15px; font-size: 1.2rem; }
            .ts-content { font-size: 1.1rem; color: #4b5563; font-style: italic; margin-bottom: 20px; }
            .ts-name { font-weight: 600; color: #111827; margin: 0; font-size: 1.1rem; }
            .ts-role { color: #6b7280; font-size: 0.9rem; margin: 4px 0 0; }
            .ts-nav { position: absolute; top: 50%; transform: translateY(-50%); background: #f3f4f6; border: none; width: 40px; height: 40px; border-radius: 50%; cursor: pointer; transition: 0.2s; z-index: 10; display: flex; align-items: center; justify-content: center; font-size: 1rem; }
            .ts-nav:hover { background: #3b82f6; color: #fff; }
            .ts-prev { left: 10px; } .ts-next { right: 10px; }
            @media (min-width: 768px) { .ts-container { padding: 50px 60px; } .ts-prev { left: 20px; } .ts-next { right: 20px; } }
        </style>

        <div class="ts-container" id="<?php echo esc_attr( $slider_id ); ?>">
            <div class="ts-wrapper">
                <?php while ( $query->have_posts() ) : $query->the_post(); 
                    $name = get_post_meta( get_the_ID(), '_testimonial_client_name', true ) ?: get_the_title();
                    $role_parts = array_filter([ 
                        get_post_meta( get_the_ID(), '_testimonial_position', true ), 
                        get_post_meta( get_the_ID(), '_testimonial_company', true ) 
                    ]);
                    $rating = (int) ( get_post_meta( get_the_ID(), '_testimonial_rating', true ) ?: 5 );
                ?>
                <div class="ts-slide <?php echo $query->current_post === 0 ? 'active' : ''; ?>">
                    <?php if ( has_post_thumbnail() ) : ?>
                        <?php the_post_thumbnail( 'thumbnail', [ 'class' => 'ts-img', 'alt' => esc_attr( $name ) ] ); ?>
                    <?php else: ?>
                        <div class="ts-img" style="background:#e5e7eb; display:inline-block;"></div>
                    <?php endif; ?>
                    <div class="ts-stars"><?php echo str_repeat( '★', $rating ) . str_repeat( '☆', 5 - $rating ); ?></div>
                    <div class="ts-content"><?php the_content(); ?></div>
                    <div class="ts-name"><?php echo esc_html( $name ); ?></div>
                    <div class="ts-role"><?php echo esc_html( implode( ', ', $role_parts ) ); ?></div>
                </div>
                <?php endwhile; ?>
            </div>
            <?php if ( $query->found_posts > 1 ) : ?>
                <button class="ts-nav ts-prev">❮</button>
                <button class="ts-nav ts-next">❯</button>
            <?php endif; ?>
        </div>
        
        <?php if ( $query->found_posts > 1 ) : ?>
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const container = document.getElementById('<?php echo esc_js( $slider_id ); ?>');
                if (!container) return;
                
                let idx = 0;
                const wrapper = container.querySelector('.ts-wrapper');
                const slides = container.querySelectorAll('.ts-slide');
                
                const update = () => {
                    wrapper.style.transform = `translateX(-${idx * 100}%)`;
                    slides.forEach((s, i) => s.classList.toggle('active', i === idx));
                };

                container.querySelector('.ts-next')?.addEventListener('click', () => { idx = (idx + 1) % slides.length; update(); });
                container.querySelector('.ts-prev')?.addEventListener('click', () => { idx = (idx - 1 + slides.length) % slides.length; update(); });
            });
        </script>
        <?php endif; wp_reset_postdata(); return ob_get_clean();
    }
}

new Testimonials_Plugin();
