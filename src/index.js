import { __ } from '@wordpress/i18n';
import { registerBlockType } from '@wordpress/blocks';
import { 
	ColorPicker,
	PanelRow,
	PanelBody, 
} from '@wordpress/components';
import { withState } from '@wordpress/compose';
import {
  RichText,
  AlignmentToolbar,
  BlockControls,
  InspectorControls,
} from '@wordpress/block-editor';

registerBlockType( 'wsubc/panel', {
  title: 'WSB: Panel',
  icon: 'format-aside',
  category: 'layout',

  attributes: {
    content: {
      type: 'array',
      source: 'children',
      selector: 'p',
    },
    alignment: {
      type: 'string',
      default: 'none',
    },
    textColor: {
    	type: 'string',
    	default: '#333',
    },
    bgColor: {
    	type: 'string',
    	default: '#e5e5e5',
    },
  },
  example: {
    attributes: {
      content: 'Hello World',
      alignment: 'left',
      textColor: '#000',
      bgColor: '#e5e5e5',
    },
  },
  
  edit: ( props ) => {
    const {
      attributes: {
        content,
        alignment,
        textColor,
        bgColor
      },
      className,
    } = props;

    const onChangeContent = ( newContent ) => {
      props.setAttributes( { content: newContent } );
    };

    const onChangeAlignment = ( newAlignment ) => {
      props.setAttributes( { alignment: newAlignment === undefined ? 'none' : newAlignment } );
    };

    const onChangeTextColor = ( newColor ) => {
    	props.setAttributes( { textColor: newColor.hex } );
    };

    const onChangeBgColor = ( newColor ) => {
    	props.setAttributes( { bgColor: newColor.hex } );
    }

    return (
      <div>
      	<InspectorControls>
	      	<PanelBody
              title={ __( 'Text Color', 'wsubusiness-companion' ) }
              initialOpen={ false }
          >
            <PanelRow>
              <ColorPicker
		            color={ textColor }
		            onChangeComplete={ onChangeTextColor }
		            disableAlpha
			        />
            </PanelRow>
          </PanelBody>
          <PanelBody
              title={ __( 'Background Color', 'wsubusiness-companion' ) }
              initialOpen={ false }
          >
            <PanelRow>
              <ColorPicker
		            color={ bgColor }
		            onChangeComplete={ onChangeBgColor }
		            disableAlpha
			        />
            </PanelRow>
          </PanelBody>
      	</InspectorControls>
        {
          <BlockControls>
            <AlignmentToolbar
              value={ alignment }
              onChange={ onChangeAlignment }
            />
          </BlockControls>
        }
        <RichText
          className={ className }
          style={ { textAlign: alignment, color: textColor, backgroundColor: bgColor } }
          tagName="p"
          onChange={ onChangeContent }
          value={ content }
        />
      </div>
    );
  },
  
  save: ( props ) => {
    return (
      <RichText.Content
        className={ `wsubc-panel-align-${ props.attributes.alignment }` }
        tagName="p"
        value={ props.attributes.content }
        style={ { color: props.attributes.textColor, backgroundColor: props.attributes.bgColor, } }
      />
    );
  },
} );