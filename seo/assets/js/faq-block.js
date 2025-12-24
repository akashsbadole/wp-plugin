/**
 * FAQ Block JavaScript for SEO Simple Plugin
 *
 * @package    Seo_Simple
 * @subpackage Seo_Simple/assets/js
 */

(function (blocks, element, components, blockEditor) {
  "use strict";

  var el = element.createElement;
  var RichText = blockEditor.RichText;
  var InspectorControls = blockEditor.InspectorControls;
  var PanelBody = components.PanelBody;
  var TextareaControl = components.TextareaControl;
  var Button = components.Button;
  var ButtonGroup = components.ButtonGroup;

  blocks.registerBlockType("seo-simple/faq", {
    title: "SEO Simple FAQ",
    icon: "format-status",
    category: "common",
    attributes: {
      questions: {
        type: "array",
        default: [],
      },
    },

    edit: function (props) {
      var attributes = props.attributes;
      var setAttributes = props.setAttributes;
      var questions = attributes.questions;

      // Add new question
      function addQuestion() {
        var newQuestions = [...questions, { question: "", answer: "" }];
        setAttributes({ questions: newQuestions });
      }

      // Update question
      function updateQuestion(index, field, value) {
        var newQuestions = [...questions];
        newQuestions[index][field] = value;
        setAttributes({ questions: newQuestions });
      }

      // Remove question
      function removeQuestion(index) {
        var newQuestions = questions.filter(function (_, i) {
          return i !== index;
        });
        setAttributes({ questions: newQuestions });
      }

      // Move question up
      function moveQuestionUp(index) {
        if (index === 0) return;
        var newQuestions = [...questions];
        var temp = newQuestions[index];
        newQuestions[index] = newQuestions[index - 1];
        newQuestions[index - 1] = temp;
        setAttributes({ questions: newQuestions });
      }

      // Move question down
      function moveQuestionDown(index) {
        if (index === questions.length - 1) return;
        var newQuestions = [...questions];
        var temp = newQuestions[index];
        newQuestions[index] = newQuestions[index + 1];
        newQuestions[index + 1] = temp;
        setAttributes({ questions: newQuestions });
      }

      return el(
        "div",
        { className: "seo-simple-faq-block-editor" },
        el(
          InspectorControls,
          null,
          el(
            PanelBody,
            { title: "FAQ Settings", initialOpen: true },
            el(
              Button,
              {
                isPrimary: true,
                onClick: addQuestion,
                className: "seo-simple-faq-add-button",
              },
              "Add New Question"
            )
          )
        ),
        el(
          "div",
          { className: "seo-simple-faq-container" },
          questions.length === 0
            ? el(
                "div",
                { className: "seo-simple-faq-empty" },
                'No FAQ items yet. Click "Add New Question" to get started.'
              )
            : questions.map(function (question, index) {
                return el(
                  "div",
                  { key: index, className: "seo-simple-faq-item" },
                  el(
                    "div",
                    { className: "seo-simple-faq-item-header" },
                    el(
                      "h4",
                      { className: "seo-simple-faq-item-title" },
                      "FAQ Item " + (index + 1)
                    ),
                    el(
                      ButtonGroup,
                      { className: "seo-simple-faq-item-controls" },
                      el(
                        Button,
                        {
                          isSmall: true,
                          onClick: function () {
                            return moveQuestionUp(index);
                          },
                          disabled: index === 0,
                          className: "seo-simple-faq-move-up",
                        },
                        "↑"
                      ),
                      el(
                        Button,
                        {
                          isSmall: true,
                          onClick: function () {
                            return moveQuestionDown(index);
                          },
                          disabled: index === questions.length - 1,
                          className: "seo-simple-faq-move-down",
                        },
                        "↓"
                      ),
                      el(
                        Button,
                        {
                          isSmall: true,
                          isDestructive: true,
                          onClick: function () {
                            return removeQuestion(index);
                          },
                          className: "seo-simple-faq-remove",
                        },
                        "Remove"
                      )
                    )
                  ),
                  el(
                    "div",
                    { className: "seo-simple-faq-item-content" },
                    el(
                      "div",
                      { className: "seo-simple-faq-field" },
                      el(
                        "label",
                        { className: "seo-simple-faq-label" },
                        "Question"
                      ),
                      el(RichText, {
                        tagName: "h4",
                        value: question.question,
                        onChange: function (value) {
                          return updateQuestion(index, "question", value);
                        },
                        placeholder: "Enter your question here...",
                        className: "seo-simple-faq-question-input",
                      })
                    ),
                    el(
                      "div",
                      { className: "seo-simple-faq-field" },
                      el(
                        "label",
                        { className: "seo-simple-faq-label" },
                        "Answer"
                      ),
                      el(RichText, {
                        tagName: "div",
                        value: question.answer,
                        onChange: function (value) {
                          return updateQuestion(index, "answer", value);
                        },
                        placeholder: "Enter your answer here...",
                        className: "seo-simple-faq-answer-input",
                      })
                    )
                  )
                );
              })
        ),
        questions.length === 0 &&
          el(
            "div",
            { className: "seo-simple-faq-add-first" },
            el(
              Button,
              {
                isPrimary: true,
                onClick: addQuestion,
                className: "seo-simple-faq-add-button",
              },
              "Add Your First FAQ Item"
            )
          )
      );
    },

    save: function (props) {
      // This block is rendered server-side
      return null;
    },
  });
})(
  window.wp.blocks,
  window.wp.element,
  window.wp.components,
  window.wp.blockEditor
);
