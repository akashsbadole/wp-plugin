<?php
/**
 * Readability Meta Box Template
 *
 * @package    Seo_Simple
 * @subpackage Seo_Simple/admin/partials
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}

$meta_prefix = $this->get_meta_key_prefix();
$readability_scores = get_post_meta( $post->ID, $meta_prefix . 'readability_scores', true );
$readability_scores = ! empty( $readability_scores ) ? json_decode( $readability_scores, true ) : array();
?>

<div class="seo-simple-meta-box readability-tab" id="seo-simple-readability-tab">
	<div class="seo-simple-readability-summary">
		<h3><?php _e( 'Readability Score', 'seo-simple' ); ?></h3>
		<div class="seo-simple-score-bar">
			<div class="seo-simple-score-fill" id="seo-simple-overall-score-fill" style="width: <?php echo isset( $readability_scores['overall']['score'] ) ? esc_attr( $readability_scores['overall']['score'] ) : '0'; ?>%; background-color: <?php echo isset( $readability_scores['overall']['status'] ) ? $this->get_status_color( $readability_scores['overall']['status'] ) : '#ddd'; ?>;"></div>
		</div>
		<div class="seo-simple-score-text">
			<span class="seo-simple-score-number"><?php echo isset( $readability_scores['overall']['score'] ) ? esc_html( $readability_scores['overall']['score'] ) : '0'; ?></span>/100
			<span class="seo-simple-score-status seo-simple-status-<?php echo isset( $readability_scores['overall']['status'] ) ? esc_attr( $readability_scores['overall']['status'] ) : 'bad'; ?>">
				<?php echo isset( $readability_scores['overall']['recommendation'] ) ? esc_html( $readability_scores['overall']['recommendation'] ) : __( 'Analyze content to get readability score.', 'seo-simple' ); ?>
			</span>
		</div>
	</div>

	<div class="seo-simple-readability-metrics">
		<h3><?php _e( 'Readability Metrics', 'seo-simple' ); ?></h3>
		
		<div class="seo-simple-metric">
			<div class="seo-simple-metric-header">
				<span class="seo-simple-metric-title"><?php _e( 'Sentence Length', 'seo-simple' ); ?></span>
				<span class="seo-simple-metric-score seo-simple-status-<?php echo isset( $readability_scores['sentence_length']['status'] ) ? esc_attr( $readability_scores['sentence_length']['status'] ) : 'bad'; ?>">
					<?php echo isset( $readability_scores['sentence_length']['score'] ) ? esc_html( $readability_scores['sentence_length']['score'] ) : '0'; ?>/100
				</span>
			</div>
			<div class="seo-simple-metric-bar">
				<div class="seo-simple-metric-fill" style="width: <?php echo isset( $readability_scores['sentence_length']['score'] ) ? esc_attr( $readability_scores['sentence_length']['score'] ) : '0'; ?>%; background-color: <?php echo isset( $readability_scores['sentence_length']['status'] ) ? $this->get_status_color( $readability_scores['sentence_length']['status'] ) : '#ddd'; ?>;"></div>
			</div>
			<div class="seo-simple-metric-info">
				<?php if ( isset( $readability_scores['sentence_length']['average'] ) ) : ?>
					<span class="seo-simple-metric-detail"><?php printf( __( 'Average: %s words per sentence', 'seo-simple' ), esc_html( $readability_scores['sentence_length']['average'] ) ); ?></span>
				<?php endif; ?>
				<div class="seo-simple-metric-recommendation">
					<?php echo isset( $readability_scores['sentence_length']['recommendation'] ) ? esc_html( $readability_scores['sentence_length']['recommendation'] ) : __( 'Analyze content to get sentence length analysis.', 'seo-simple' ); ?>
				</div>
			</div>
		</div>

		<div class="seo-simple-metric">
			<div class="seo-simple-metric-header">
				<span class="seo-simple-metric-title"><?php _e( 'Paragraph Length', 'seo-simple' ); ?></span>
				<span class="seo-simple-metric-score seo-simple-status-<?php echo isset( $readability_scores['paragraph_length']['status'] ) ? esc_attr( $readability_scores['paragraph_length']['status'] ) : 'bad'; ?>">
					<?php echo isset( $readability_scores['paragraph_length']['score'] ) ? esc_html( $readability_scores['paragraph_length']['score'] ) : '0'; ?>/100
				</span>
			</div>
			<div class="seo-simple-metric-bar">
				<div class="seo-simple-metric-fill" style="width: <?php echo isset( $readability_scores['paragraph_length']['score'] ) ? esc_attr( $readability_scores['paragraph_length']['score'] ) : '0'; ?>%; background-color: <?php echo isset( $readability_scores['paragraph_length']['status'] ) ? $this->get_status_color( $readability_scores['paragraph_length']['status'] ) : '#ddd'; ?>;"></div>
			</div>
			<div class="seo-simple-metric-info">
				<?php if ( isset( $readability_scores['paragraph_length']['total_paragraphs'] ) ) : ?>
					<span class="seo-simple-metric-detail"><?php printf( __( '%d total paragraphs, %d too long', 'seo-simple' ), esc_html( $readability_scores['paragraph_length']['total_paragraphs'] ), esc_html( $readability_scores['paragraph_length']['long_paragraphs'] ) ); ?></span>
				<?php endif; ?>
				<div class="seo-simple-metric-recommendation">
					<?php echo isset( $readability_scores['paragraph_length']['recommendation'] ) ? esc_html( $readability_scores['paragraph_length']['recommendation'] ) : __( 'Analyze content to get paragraph length analysis.', 'seo-simple' ); ?>
				</div>
			</div>
		</div>

		<div class="seo-simple-metric">
			<div class="seo-simple-metric-header">
				<span class="seo-simple-metric-title"><?php _e( 'Subheading Distribution', 'seo-simple' ); ?></span>
				<span class="seo-simple-metric-score seo-simple-status-<?php echo isset( $readability_scores['subheading_distribution']['status'] ) ? esc_attr( $readability_scores['subheading_distribution']['status'] ) : 'bad'; ?>">
					<?php echo isset( $readability_scores['subheading_distribution']['score'] ) ? esc_html( $readability_scores['subheading_distribution']['score'] ) : '0'; ?>/100
				</span>
			</div>
			<div class="seo-simple-metric-bar">
				<div class="seo-simple-metric-fill" style="width: <?php echo isset( $readability_scores['subheading_distribution']['score'] ) ? esc_attr( $readability_scores['subheading_distribution']['score'] ) : '0'; ?>%; background-color: <?php echo isset( $readability_scores['subheading_distribution']['status'] ) ? $this->get_status_color( $readability_scores['subheading_distribution']['status'] ) : '#ddd'; ?>;"></div>
			</div>
			<div class="seo-simple-metric-info">
				<?php if ( isset( $readability_scores['subheading_distribution']['h2_count'] ) ) : ?>
					<span class="seo-simple-metric-detail"><?php printf( __( '%d H2 headings, %d H3 headings', 'seo-simple' ), esc_html( $readability_scores['subheading_distribution']['h2_count'] ), esc_html( $readability_scores['subheading_distribution']['h3_count'] ) ); ?></span>
				<?php endif; ?>
				<div class="seo-simple-metric-recommendation">
					<?php echo isset( $readability_scores['subheading_distribution']['recommendation'] ) ? esc_html( $readability_scores['subheading_distribution']['recommendation'] ) : __( 'Analyze content to get subheading distribution analysis.', 'seo-simple' ); ?>
				</div>
			</div>
		</div>

		<div class="seo-simple-metric">
			<div class="seo-simple-metric-header">
				<span class="seo-simple-metric-title"><?php _e( 'Passive Voice', 'seo-simple' ); ?></span>
				<span class="seo-simple-metric-score seo-simple-status-<?php echo isset( $readability_scores['passive_voice']['status'] ) ? esc_attr( $readability_scores['passive_voice']['status'] ) : 'bad'; ?>">
					<?php echo isset( $readability_scores['passive_voice']['score'] ) ? esc_html( $readability_scores['passive_voice']['score'] ) : '0'; ?>/100
				</span>
			</div>
			<div class="seo-simple-metric-bar">
				<div class="seo-simple-metric-fill" style="width: <?php echo isset( $readability_scores['passive_voice']['score'] ) ? esc_attr( $readability_scores['passive_voice']['score'] ) : '0'; ?>%; background-color: <?php echo isset( $readability_scores['passive_voice']['status'] ) ? $this->get_status_color( $readability_scores['passive_voice']['status'] ) : '#ddd'; ?>;"></div>
			</div>
			<div class="seo-simple-metric-info">
				<?php if ( isset( $readability_scores['passive_voice']['percentage_passive'] ) ) : ?>
					<span class="seo-simple-metric-detail"><?php printf( __( '%s%% passive voice usage', 'seo-simple' ), esc_html( $readability_scores['passive_voice']['percentage_passive'] ) ); ?></span>
				<?php endif; ?>
				<div class="seo-simple-metric-recommendation">
					<?php echo isset( $readability_scores['passive_voice']['recommendation'] ) ? esc_html( $readability_scores['passive_voice']['recommendation'] ) : __( 'Analyze content to get passive voice analysis.', 'seo-simple' ); ?>
				</div>
			</div>
		</div>

		<div class="seo-simple-metric">
			<div class="seo-simple-metric-header">
				<span class="seo-simple-metric-title"><?php _e( 'Transition Words', 'seo-simple' ); ?></span>
				<span class="seo-simple-metric-score seo-simple-status-<?php echo isset( $readability_scores['transition_words']['status'] ) ? esc_attr( $readability_scores['transition_words']['status'] ) : 'bad'; ?>">
					<?php echo isset( $readability_scores['transition_words']['score'] ) ? esc_html( $readability_scores['transition_words']['score'] ) : '0'; ?>/100
				</span>
			</div>
			<div class="seo-simple-metric-bar">
				<div class="seo-simple-metric-fill" style="width: <?php echo isset( $readability_scores['transition_words']['score'] ) ? esc_attr( $readability_scores['transition_words']['score'] ) : '0'; ?>%; background-color: <?php echo isset( $readability_scores['transition_words']['status'] ) ? $this->get_status_color( $readability_scores['transition_words']['status'] ) : '#ddd'; ?>;"></div>
			</div>
			<div class="seo-simple-metric-info">
				<?php if ( isset( $readability_scores['transition_words']['percentage_transition'] ) ) : ?>
					<span class="seo-simple-metric-detail"><?php printf( __( '%s%% transition words usage', 'seo-simple' ), esc_html( $readability_scores['transition_words']['percentage_transition'] ) ); ?></span>
				<?php endif; ?>
				<div class="seo-simple-metric-recommendation">
					<?php echo isset( $readability_scores['transition_words']['recommendation'] ) ? esc_html( $readability_scores['transition_words']['recommendation'] ) : __( 'Analyze content to get transition words analysis.', 'seo-simple' ); ?>
				</div>
			</div>
		</div>

		<div class="seo-simple-metric">
			<div class="seo-simple-metric-header">
				<span class="seo-simple-metric-title"><?php _e( 'Flesch Reading Ease', 'seo-simple' ); ?></span>
				<span class="seo-simple-metric-score seo-simple-status-<?php echo isset( $readability_scores['flesch_reading_ease']['status'] ) ? esc_attr( $readability_scores['flesch_reading_ease']['status'] ) : 'bad'; ?>">
					<?php echo isset( $readability_scores['flesch_reading_ease']['score'] ) ? esc_html( $readability_scores['flesch_reading_ease']['score'] ) : '0'; ?>/100
				</span>
			</div>
			<div class="seo-simple-metric-bar">
				<div class="seo-simple-metric-fill" style="width: <?php echo isset( $readability_scores['flesch_reading_ease']['score'] ) ? esc_attr( $readability_scores['flesch_reading_ease']['score'] ) : '0'; ?>%; background-color: <?php echo isset( $readability_scores['flesch_reading_ease']['status'] ) ? $this->get_status_color( $readability_scores['flesch_reading_ease']['status'] ) : '#ddd'; ?>;"></div>
			</div>
			<div class="seo-simple-metric-info">
				<div class="seo-simple-metric-recommendation">
					<?php echo isset( $readability_scores['flesch_reading_ease']['recommendation'] ) ? esc_html( $readability_scores['flesch_reading_ease']['recommendation'] ) : __( 'Analyze content to get Flesch Reading Ease analysis.', 'seo-simple' ); ?>
				</div>
			</div>
		</div>
	</div>

	<div class="seo-simple-readability-actions">
		<button type="button" class="button button-secondary" id="seo-simple-analyze-readability">
			<?php _e( 'Analyze Readability', 'seo-simple' ); ?>
		</button>
		<span class="spinner" id="seo-simple-readability-spinner" style="display: none;"></span>
	</div>
</div>

<style>
.seo-simple-readability-summary {
	margin-bottom: 20px;
	padding: 15px;
	background-color: #f9f9f9;
	border: 1px solid #ddd;
	border-radius: 4px;
}

.seo-simple-score-bar {
	height: 20px;
	background-color: #ddd;
	border-radius: 10px;
	margin-bottom: 10px;
	overflow: hidden;
}

.seo-simple-score-fill {
	height: 100%;
	transition: width 0.5s ease;
}

.seo-simple-score-text {
	display: flex;
	justify-content: space-between;
	align-items: center;
}

.seo-simple-score-number {
	font-size: 24px;
	font-weight: bold;
}

.seo-simple-status-good {
	color: #46b450;
}

.seo-simple-status-ok {
	color: #ffb900;
}

.seo-simple-status-bad {
	color: #dc3232;
}

.seo-simple-readability-metrics h3 {
	margin-top: 20px;
	margin-bottom: 15px;
	font-size: 16px;
}

.seo-simple-metric {
	margin-bottom: 20px;
	padding: 15px;
	border: 1px solid #ddd;
	border-radius: 4px;
	background-color: #fff;
}

.seo-simple-metric-header {
	display: flex;
	justify-content: space-between;
	align-items: center;
	margin-bottom: 10px;
}

.seo-simple-metric-title {
	font-weight: bold;
}

.seo-simple-metric-score {
	font-weight: bold;
}

.seo-simple-metric-bar {
	height: 10px;
	background-color: #ddd;
	border-radius: 5px;
	margin-bottom: 10px;
	overflow: hidden;
}

.seo-simple-metric-fill {
	height: 100%;
	transition: width 0.5s ease;
}

.seo-simple-metric-info {
	font-size: 13px;
}

.seo-simple-metric-detail {
	display: block;
	margin-bottom: 5px;
	color: #666;
}

.seo-simple-metric-recommendation {
	font-style: italic;
	color: #555;
}

.seo-simple-readability-actions {
	margin-top: 20px;
	text-align: center;
}

.seo-simple-readability-actions .spinner {
	float: none;
	margin: 0 0 0 10px;
	vertical-align: middle;
}
</style>
