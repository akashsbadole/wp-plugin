<?php
/**
 * Recipe Schema Fields Template
 *
 * @package    Seo_Simple
 * @subpackage Seo_Simple/admin/partials/schema-fields
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}

$recipe_name = get_post_meta( $post->ID, '_seo_simple_recipe_name', true );
$recipe_description = get_post_meta( $post->ID, '_seo_simple_recipe_description', true );
$recipe_image = get_post_meta( $post->ID, '_seo_simple_recipe_image', true );
$recipe_prep_time = get_post_meta( $post->ID, '_seo_simple_recipe_prep_time', true );
$recipe_cook_time = get_post_meta( $post->ID, '_seo_simple_recipe_cook_time', true );
$recipe_total_time = get_post_meta( $post->ID, '_seo_simple_recipe_total_time', true );
$recipe_yield = get_post_meta( $post->ID, '_seo_simple_recipe_yield', true );
$recipe_category = get_post_meta( $post->ID, '_seo_simple_recipe_category', true );
$recipe_cuisine = get_post_meta( $post->ID, '_seo_simple_recipe_cuisine', true );
$recipe_keywords = get_post_meta( $post->ID, '_seo_simple_recipe_keywords', true );
$recipe_ingredients = get_post_meta( $post->ID, '_seo_simple_recipe_ingredients', true );
$recipe_instructions = get_post_meta( $post->ID, '_seo_simple_recipe_instructions', true );
$recipe_calories = get_post_meta( $post->ID, '_seo_simple_recipe_calories', true );
$recipe_fat = get_post_meta( $post->ID, '_seo_simple_recipe_fat', true );
?>

<div class="seo-simple-field-group">
	<h4><?php _e( 'Recipe Information', 'seo-simple' ); ?></h4>
	
	<div class="seo-simple-field">
		<label for="_seo_simple_recipe_name"><?php _e( 'Recipe Name', 'seo-simple' ); ?> <span class="seo-simple-field-required">*</span></label>
		<input 
			type="text" 
			id="_seo_simple_recipe_name" 
			name="_seo_simple_recipe_name" 
			value="<?php echo esc_attr( $recipe_name ); ?>" 
			class="widefat"
			placeholder="<?php esc_attr_e( 'Enter recipe name', 'seo-simple' ); ?>">
		<p class="description"><?php _e( 'The name of the recipe.', 'seo-simple' ); ?></p>
	</div>
	
	<div class="seo-simple-field">
		<label for="_seo_simple_recipe_description"><?php _e( 'Description', 'seo-simple' ); ?></label>
		<textarea 
			id="_seo_simple_recipe_description" 
			name="_seo_simple_recipe_description" 
			class="widefat seo-simple-textarea"
			rows="3"
			placeholder="<?php esc_attr_e( 'Enter recipe description', 'seo-simple' ); ?>"><?php echo esc_textarea( $recipe_description ); ?></textarea>
		<p class="description"><?php _e( 'A brief description of the recipe.', 'seo-simple' ); ?></p>
	</div>
	
	<div class="seo-simple-field">
		<label for="_seo_simple_recipe_image"><?php _e( 'Recipe Image', 'seo-simple' ); ?></label>
		<div class="seo-simple-field-row">
			<input 
				type="url" 
				id="_seo_simple_recipe_image" 
				name="_seo_simple_recipe_image" 
				value="<?php echo esc_attr( $recipe_image ); ?>" 
				class="widefat seo-simple-url-input"
				placeholder="https://example.com/recipe-image.jpg">
			<button type="button" class="button seo-simple-media-uploader" data-target="_seo_simple_recipe_image" data-title="<?php esc_attr_e( 'Select Recipe Image', 'seo-simple' ); ?>">
				<?php _e( 'Upload', 'seo-simple' ); ?>
			</button>
		</div>
		<?php if ( ! empty( $recipe_image ) ) : ?>
			<img id="_seo_simple_recipe_image-preview" src="<?php echo esc_url( $recipe_image ); ?>" class="seo-simple-media-preview" alt="<?php esc_attr_e( 'Recipe Image Preview', 'seo-simple' ); ?>">
		<?php else : ?>
			<img id="_seo_simple_recipe_image-preview" src="" class="seo-simple-media-preview" style="display: none;" alt="<?php esc_attr_e( 'Recipe Image Preview', 'seo-simple' ); ?>">
		<?php endif; ?>
		<p class="description"><?php _e( 'A high-quality image of the finished recipe.', 'seo-simple' ); ?></p>
	</div>
</div>

<div class="seo-simple-field-group">
	<h4><?php _e( 'Cooking Times', 'seo-simple' ); ?></h4>
	
	<div class="seo-simple-field-row">
		<div class="seo-simple-field">
			<label for="_seo_simple_recipe_prep_time"><?php _e( 'Preparation Time', 'seo-simple' ); ?></label>
			<input 
				type="text" 
				id="_seo_simple_recipe_prep_time" 
				name="_seo_simple_recipe_prep_time" 
				value="<?php echo esc_attr( $recipe_prep_time ); ?>" 
				class="widefat"
				placeholder="PT15M">
			<p class="description"><?php _e( 'The time it takes to prepare the recipe (ISO 8601 format, e.g., PT15M for 15 minutes).', 'seo-simple' ); ?></p>
		</div>
		
		<div class="seo-simple-field">
			<label for="_seo_simple_recipe_cook_time"><?php _e( 'Cook Time', 'seo-simple' ); ?></label>
			<input 
				type="text" 
				id="_seo_simple_recipe_cook_time" 
				name="_seo_simple_recipe_cook_time" 
				value="<?php echo esc_attr( $recipe_cook_time ); ?>" 
				class="widefat"
				placeholder="PT30M">
			<p class="description"><?php _e( 'The time it takes to cook the recipe (ISO 8601 format, e.g., PT30M for 30 minutes).', 'seo-simple' ); ?></p>
		</div>
	</div>
	
	<div class="seo-simple-field-row">
		<div class="seo-simple-field">
			<label for="_seo_simple_recipe_total_time"><?php _e( 'Total Time', 'seo-simple' ); ?></label>
			<input 
				type="text" 
				id="_seo_simple_recipe_total_time" 
				name="_seo_simple_recipe_total_time" 
				value="<?php echo esc_attr( $recipe_total_time ); ?>" 
				class="widefat"
				placeholder="PT45M">
			<p class="description"><?php _e( 'The total time to make the recipe (ISO 8601 format, e.g., PT45M for 45 minutes).', 'seo-simple' ); ?></p>
		</div>
		
		<div class="seo-simple-field">
			<label for="_seo_simple_recipe_yield"><?php _e( 'Yield/Servings', 'seo-simple' ); ?></label>
			<input 
				type="text" 
				id="_seo_simple_recipe_yield" 
				name="_seo_simple_recipe_yield" 
				value="<?php echo esc_attr( $recipe_yield ); ?>" 
				class="widefat"
				placeholder="4 servings">
			<p class="description"><?php _e( 'The quantity produced by the recipe (e.g., "4 servings", "1 loaf").', 'seo-simple' ); ?></p>
		</div>
	</div>
</div>

<div class="seo-simple-field-group">
	<h4><?php _e( 'Recipe Categories', 'seo-simple' ); ?></h4>
	
	<div class="seo-simple-field-row">
		<div class="seo-simple-field">
			<label for="_seo_simple_recipe_category"><?php _e( 'Recipe Category', 'seo-simple' ); ?></label>
			<select 
				id="_seo_simple_recipe_category" 
				name="_seo_simple_recipe_category" 
				class="widefat seo-simple-select">
				<option value=""><?php _e( 'Select category', 'seo-simple' ); ?></option>
				<option value="Appetizer" <?php selected( $recipe_category, 'Appetizer' ); ?>><?php _e( 'Appetizer', 'seo-simple' ); ?></option>
				<option value="Main Course" <?php selected( $recipe_category, 'Main Course' ); ?>><?php _e( 'Main Course', 'seo-simple' ); ?></option>
				<option value="Dessert" <?php selected( $recipe_category, 'Dessert' ); ?>><?php _e( 'Dessert', 'seo-simple' ); ?></option>
				<option value="Side Dish" <?php selected( $recipe_category, 'Side Dish' ); ?>><?php _e( 'Side Dish', 'seo-simple' ); ?></option>
				<option value="Salad" <?php selected( $recipe_category, 'Salad' ); ?>><?php _e( 'Salad', 'seo-simple' ); ?></option>
				<option value="Soup" <?php selected( $recipe_category, 'Soup' ); ?>><?php _e( 'Soup', 'seo-simple' ); ?></option>
				<option value="Beverage" <?php selected( $recipe_category, 'Beverage' ); ?>><?php _e( 'Beverage', 'seo-simple' ); ?></option>
				<option value="Breakfast" <?php selected( $recipe_category, 'Breakfast' ); ?>><?php _e( 'Breakfast', 'seo-simple' ); ?></option>
				<option value="Snack" <?php selected( $recipe_category, 'Snack' ); ?>><?php _e( 'Snack', 'seo-simple' ); ?></option>
			</select>
			<p class="description"><?php _e( 'The category of the recipe.', 'seo-simple' ); ?></p>
		</div>
		
		<div class="seo-simple-field">
			<label for="_seo_simple_recipe_cuisine"><?php _e( 'Cuisine', 'seo-simple' ); ?></label>
			<select 
				id="_seo_simple_recipe_cuisine" 
				name="_seo_simple_recipe_cuisine" 
				class="widefat seo-simple-select">
				<option value=""><?php _e( 'Select cuisine', 'seo-simple' ); ?></option>
				<option value="American" <?php selected( $recipe_cuisine, 'American' ); ?>><?php _e( 'American', 'seo-simple' ); ?></option>
				<option value="Italian" <?php selected( $recipe_cuisine, 'Italian' ); ?>><?php _e( 'Italian', 'seo-simple' ); ?></option>
				<option value="Chinese" <?php selected( $recipe_cuisine, 'Chinese' ); ?>><?php _e( 'Chinese', 'seo-simple' ); ?></option>
				<option value="Mexican" <?php selected( $recipe_cuisine, 'Mexican' ); ?>><?php _e( 'Mexican', 'seo-simple' ); ?></option>
				<option value="Indian" <?php selected( $recipe_cuisine, 'Indian' ); ?>><?php _e( 'Indian', 'seo-simple' ); ?></option>
				<option value="Japanese" <?php selected( $recipe_cuisine, 'Japanese' ); ?>><?php _e( 'Japanese', 'seo-simple' ); ?></option>
				<option value="Thai" <?php selected( $recipe_cuisine, 'Thai' ); ?>><?php _e( 'Thai', 'seo-simple' ); ?></option>
				<option value="French" <?php selected( $recipe_cuisine, 'French' ); ?>><?php _e( 'French', 'seo-simple' ); ?></option>
				<option value="Mediterranean" <?php selected( $recipe_cuisine, 'Mediterranean' ); ?>><?php _e( 'Mediterranean', 'seo-simple' ); ?></option>
			</select>
			<p class="description"><?php _e( 'The cuisine type of the recipe.', 'seo-simple' ); ?></p>
		</div>
	</div>
	
	<div class="seo-simple-field">
		<label for="_seo_simple_recipe_keywords"><?php _e( 'Keywords', 'seo-simple' ); ?></label>
		<input 
			type="text" 
			id="_seo_simple_recipe_keywords" 
			name="_seo_simple_recipe_keywords" 
			value="<?php echo esc_attr( $recipe_keywords ); ?>" 
			class="widefat"
			placeholder="easy, healthy, vegetarian">
		<p class="description"><?php _e( 'Comma-separated keywords for the recipe.', 'seo-simple' ); ?></p>
	</div>
</div>

<div class="seo-simple-field-group">
	<h4><?php _e( 'Ingredients', 'seo-simple' ); ?></h4>
	
	<div class="seo-simple-field">
		<label for="_seo_simple_recipe_ingredients"><?php _e( 'Ingredients List', 'seo-simple' ); ?> <span class="seo-simple-field-required">*</span></label>
		<textarea 
			id="_seo_simple_recipe_ingredients" 
			name="_seo_simple_recipe_ingredients" 
			class="widefat seo-simple-textarea"
			rows="8"
			placeholder="<?php esc_attr_e( 'Enter one ingredient per line', 'seo-simple' ); ?>"><?php echo esc_textarea( $recipe_ingredients ); ?></textarea>
		<p class="description"><?php _e( 'Enter each ingredient on a separate line. Include quantities and units.', 'seo-simple' ); ?></p>
	</div>
</div>

<div class="seo-simple-field-group">
	<h4><?php _e( 'Instructions', 'seo-simple' ); ?></h4>
	
	<div class="seo-simple-field">
		<label for="_seo_simple_recipe_instructions"><?php _e( 'Cooking Instructions', 'seo-simple' ); ?> <span class="seo-simple-field-required">*</span></label>
		<textarea 
			id="_seo_simple_recipe_instructions" 
			name="_seo_simple_recipe_instructions" 
			class="widefat seo-simple-textarea"
			rows="10"
			placeholder="<?php esc_attr_e( 'Enter each instruction step on a separate line', 'seo-simple' ); ?>"><?php echo esc_textarea( $recipe_instructions ); ?></textarea>
		<p class="description"><?php _e( 'Enter each cooking instruction step on a separate line. The steps will be numbered automatically.', 'seo-simple' ); ?></p>
	</div>
</div>

<div class="seo-simple-field-group">
	<h4><?php _e( 'Nutrition Information', 'seo-simple' ); ?></h4>
	
	<div class="seo-simple-field-row">
		<div class="seo-simple-field">
			<label for="_seo_simple_recipe_calories"><?php _e( 'Calories', 'seo-simple' ); ?></label>
			<input 
				type="number" 
				id="_seo_simple_recipe_calories" 
				name="_seo_simple_recipe_calories" 
				value="<?php echo esc_attr( $recipe_calories ); ?>" 
				class="widefat seo-simple-number-input"
				min="0"
				placeholder="250">
			<p class="description"><?php _e( 'Calories per serving.', 'seo-simple' ); ?></p>
		</div>
		
		<div class="seo-simple-field">
			<label for="_seo_simple_recipe_fat"><?php _e( 'Fat Content (g)', 'seo-simple' ); ?></label>
			<input 
				type="number" 
				id="_seo_simple_recipe_fat" 
				name="_seo_simple_recipe_fat" 
				value="<?php echo esc_attr( $recipe_fat ); ?>" 
				class="widefat seo-simple-number-input"
				min="0"
				step="0.1"
				placeholder="10">
			<p class="description"><?php _e( 'Total fat content in grams per serving.', 'seo-simple' ); ?></p>
		</div>
	</div>
</div>