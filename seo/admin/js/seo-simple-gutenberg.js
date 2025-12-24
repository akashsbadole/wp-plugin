/**
 * Gutenberg integration for SEO Simple plugin.
 *
 * @link       https://example.com
 * @since      1.0.0
 *
 * @package    Seo_Simple
 * @subpackage Seo_Simple/admin/js
 */

(function (wp, $) {
  const { registerPlugin } = wp.plugins;
  const { PluginSidebar, PluginSidebarMoreMenuItem } = wp.editPost;
  const {
    PanelBody,
    TextControl,
    TextareaControl,
    CheckboxControl,
    ToggleControl,
    Button,
    Spinner,
  } = wp.components;
  const { withSelect, withDispatch } = wp.data;
  const { compose } = wp.compose;
  const { Component, Fragment } = wp.element;
  const { __ } = wp.i18n;

  /**
   * SEO Meta Sidebar Component.
   */
  const SEOMetaSidebar = compose(
    withSelect((select) => {
      const meta = select("core/editor").getEditedPostAttribute("meta");
      return {
        meta,
        title: select("core/editor").getEditedPostAttribute("title"),
        excerpt: select("core/editor").getEditedPostAttribute("excerpt"),
        content: select("core/editor").getEditedPostAttribute("content"),
        postId: select("core/editor").getCurrentPostId(),
        postType: select("core/editor").getCurrentPostType(),
        isSaving: select("core/editor").isSavingPost(),
      };
    }),
    withDispatch((dispatch) => ({
      updateMeta: (newMeta) => {
        dispatch("core/editor").editPost({ meta: newMeta });
      },
    }))
  )(
    class SEOMetaSidebar extends Component {
      constructor(props) {
        super(props);
        this.state = {
          loading: false,
          analysis: null,
          socialImagePreview: "",
        };
      }

      componentDidMount() {
        this.loadAnalysis();
        this.loadSocialImagePreview();
      }
      
      componentDidUpdate(prevProps) {
        // Check if title has changed
        if (prevProps.title !== this.props.title) {
          const { meta } = this.props;
          const currentSeoTitle = meta._seo_simple_title || "";
          
          // Only auto-update if SEO title is empty
          if (!currentSeoTitle) {
            this.updateMeta("title", this.props.title);
          }
        }
      }

      loadAnalysis() {
        const { postId } = this.props;

        if (!postId) {
          return;
        }

        this.setState({ loading: true });

        $.ajax({
          url: seo_simple_gutenberg.ajax_url,
          type: "POST",
          data: {
            action: "seo_simple_get_analysis",
            post_id: postId,
            nonce: seo_simple_gutenberg.nonce,
          },
          success: (response) => {
            if (response.success) {
              this.setState({ analysis: response.data });
            } else {
              console.error("Error getting analysis:", response.data && response.data.message ? response.data.message : "Unknown error");
            }
          },
          error: (xhr, status, error) => {
            console.error("AJAX error getting analysis:", error);
          },
          complete: () => {
            this.setState({ loading: false });
          },
        });
      }

      loadSocialImagePreview() {
        const { meta } = this.props;
        const socialImage = meta._seo_simple_social_image || "";

        if (socialImage) {
          this.setState({ socialImagePreview: socialImage });
        }
      }

      updateMeta(key, value) {
        const { meta, updateMeta } = this.props;
        const newMeta = { ...meta };

        newMeta[`_seo_simple_${key}`] = value;
        updateMeta(newMeta);
      }

      runAnalysis(type) {
        const { postId, title, excerpt, content, meta } = this.props;

        if (!postId) {
          return;
        }

        this.setState({ loading: true });

        const data = {
          action:
            type === "readability"
              ? "seo_simple_analyze_readability"
              : "seo_simple_analyze_seo",
          post_id: postId,
          title: meta._seo_simple_title || title,
          description: meta._seo_simple_description || excerpt,
          content: content,
          focus_keyword: meta._seo_simple_focus_keyword || "",
          nonce: seo_simple_gutenberg.nonce,
        };

        $.ajax({
          url: seo_simple_gutenberg.ajax_url,
          type: "POST",
          data: data,
          success: (response) => {
            if (response.success) {
              this.loadAnalysis();
            } else {
              console.error("Error running analysis:", response.data && response.data.message ? response.data.message : "Unknown error");
            }
          },
          error: (xhr, status, error) => {
            console.error("AJAX error running analysis:", error);
          },
          complete: () => {
            this.setState({ loading: false });
          },
        });
      }

      selectSocialImage() {
        const frame = wp.media({
          title: __("Select Social Image", "seo-simple"),
          multiple: false,
          library: { type: "image" },
        });

        frame.on("select", () => {
          const attachment = frame.state().get("selection").first().toJSON();
          this.updateMeta("social_image", attachment.url);
          this.updateMeta("social_image_id", attachment.id);
          this.setState({ socialImagePreview: attachment.url });
        });

        frame.open();
      }

      getScoreDisplay(score, type) {
        if (!score || !score.overall) {
          return null;
        }

        const { score: value, status, recommendation } = score.overall;
        let color = "#dc3232"; // bad

        if (status === "ok") {
          color = "#ffb900";
        } else if (status === "good") {
          color = "#46b450";
        }

        return (
          <div className="seo-simple-score">
            <div className="seo-simple-score-value" style={{ color }}>
              {value}%
            </div>
            <div className="seo-simple-score-status">{recommendation}</div>
          </div>
        );
      }

      render() {
        const { meta, title, excerpt, postType, isSaving } = this.props;
        const { loading, analysis, socialImagePreview } = this.state;

        return (
          <Fragment>
            <PluginSidebar
              name="seo-simple-sidebar"
              title={__("SEO Settings", "seo-simple")}
            >
              <PanelBody title={__("SEO", "seo-simple")} initialOpen={true}>
                <TextControl
                  label={__("SEO Title", "seo-simple")}
                  value={meta._seo_simple_title || ""}
                  onChange={(value) => this.updateMeta("title", value)}
                  placeholder={title}
                />

                <TextareaControl
                  label={__("SEO Description", "seo-simple")}
                  value={meta._seo_simple_description || ""}
                  onChange={(value) => this.updateMeta("description", value)}
                  placeholder={excerpt}
                />

                <TextControl
                  label={__("Focus Keyword", "seo-simple")}
                  value={meta._seo_simple_focus_keyword || ""}
                  onChange={(value) => this.updateMeta("focus_keyword", value)}
                />

                <TextControl
                  label={__("Canonical URL", "seo-simple")}
                  value={meta._seo_simple_canonical || ""}
                  onChange={(value) => this.updateMeta("canonical", value)}
                  placeholder={seo_simple_gutenberg.plugin_url}
                />

                <ToggleControl
                  label={__("Noindex", "seo-simple")}
                  checked={!!meta._seo_simple_noindex}
                  onChange={(value) =>
                    this.updateMeta("noindex", value ? 1 : 0)
                  }
                />

                <ToggleControl
                  label={__("Nofollow", "seo-simple")}
                  checked={!!meta._seo_simple_nofollow}
                  onChange={(value) =>
                    this.updateMeta("nofollow", value ? 1 : 0)
                  }
                />

                {postType === "product" && (
                  <Fragment>
                    <TextControl
                      label={__("Product SEO Title", "seo-simple")}
                      value={meta._seo_simple_product_title || ""}
                      onChange={(value) =>
                        this.updateMeta("product_title", value)
                      }
                    />

                    <TextareaControl
                      label={__("Product SEO Description", "seo-simple")}
                      value={meta._seo_simple_product_description || ""}
                      onChange={(value) =>
                        this.updateMeta("product_description", value)
                      }
                    />
                  </Fragment>
                )}

                <Button
                  isPrimary
                  onClick={() => this.runAnalysis("seo")}
                  disabled={loading || isSaving}
                  isBusy={loading}
                >
                  {__("Analyze SEO", "seo-simple")}
                </Button>
              </PanelBody>

              <PanelBody title={__("Social", "seo-simple")} initialOpen={false}>
                <ToggleControl
                  label={__("Use SEO Data", "seo-simple")}
                  checked={!!meta._seo_simple_use_seo_data}
                  onChange={(value) =>
                    this.updateMeta("use_seo_data", value ? 1 : 0)
                  }
                />

                <TextControl
                  label={__("Social Title", "seo-simple")}
                  value={meta._seo_simple_social_title || ""}
                  onChange={(value) => this.updateMeta("social_title", value)}
                />

                <TextareaControl
                  label={__("Social Description", "seo-simple")}
                  value={meta._seo_simple_social_description || ""}
                  onChange={(value) =>
                    this.updateMeta("social_description", value)
                  }
                />

                <div className="seo-simple-social-image">
                  <p className="components-base-control__label">
                    {__("Social Image", "seo-simple")}
                  </p>
                  {socialImagePreview && (
                    <img
                      src={socialImagePreview}
                      alt={__("Social Image Preview", "seo-simple")}
                      style={{
                        maxWidth: "100%",
                        height: "auto",
                        marginBottom: "10px",
                      }}
                    />
                  )}
                  <Button isSecondary onClick={() => this.selectSocialImage()}>
                    {__("Select Image", "seo-simple")}
                  </Button>
                </div>
              </PanelBody>

              <PanelBody
                title={__("Analysis", "seo-simple")}
                initialOpen={false}
              >
                {loading && <Spinner />}

                {analysis && (
                  <Fragment>
                    <h4>{__("SEO Analysis", "seo-simple")}</h4>
                    {this.getScoreDisplay(analysis.seo, "seo")}

                    <h4>{__("Readability Analysis", "seo-simple")}</h4>
                    {this.getScoreDisplay(analysis.readability, "readability")}

                    <Button
                      isSecondary
                      onClick={() => this.runAnalysis("readability")}
                      disabled={loading || isSaving}
                      isBusy={loading}
                    >
                      {__("Analyze Readability", "seo-simple")}
                    </Button>
                  </Fragment>
                )}

                {!analysis && !loading && (
                  <p>
                    {__(
                      'No analysis available. Click "Analyze SEO" to generate one.',
                      "seo-simple"
                    )}
                  </p>
                )}
              </PanelBody>
            </PluginSidebar>

            <PluginSidebarMoreMenuItem target="seo-simple-sidebar">
              {__("SEO Settings", "seo-simple")}
            </PluginSidebarMoreMenuItem>
          </Fragment>
        );
      }
    }
  );

  // Register the plugin
  registerPlugin("seo-simple-gutenberg", {
    icon: "search",
    render: SEOMetaSidebar,
  });
})(window.wp, jQuery);
