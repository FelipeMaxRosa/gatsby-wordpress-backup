<?php

namespace WPGraphQL\Type;

use GraphQL\Type\Definition\UnionType;
use WPGraphQL\Registry\TypeRegistry;

/**
 * Class WPUnionType
 *
 * Union Types should extend this class to take advantage of the helper methods
 * and consistent filters.
 *
 * @package WPGraphQL\Type\Union
 * @since   0.0.30
 */
class WPUnionType extends UnionType {

	/**
	 * @var TypeRegistry
	 */
	public $type_registry;

	/**
	 * WPUnionType constructor.
	 *
	 * @param array        $config The Config to setup a Union Type
	 * @param TypeRegistry $type_registry
	 *
	 * @since 0.0.30
	 */
	public function __construct( $config = [], TypeRegistry $type_registry ) {

		$this->type_registry = $type_registry;

		/**
		 * Set the Types to start with capitals
		 */
		$config['name'] = ucfirst( $config['name'] );

		$config['types'] = function() use ( $config ) {
			$prepared_types = [];
			if ( ! empty( $config['typeNames'] ) && is_array( $config['typeNames'] ) ) {
				$prepared_types = [];
				foreach ( $config['typeNames'] as $type_name ) {
					$prepared_types[] = $this->type_registry->get_type( $type_name );
				}
			}
			return $prepared_types;
		};

		$config['resolveType'] = function( $object ) use ( $config ) {
			$type = null;
			if ( is_callable( $config['resolveType'] ) ) {
				$type = call_user_func( $config['resolveType'], $object );
			}
			/**
			 * Filter the resolve type method for all unions
			 *
			 * @param mixed $type The Type to resolve to, based on the object being resolved
			 * @param mixed $object The Object being resolved
			 * @param WPUnionType $this The WPUnionType instance
			 */
			return apply_filters( 'graphql_union_resolve_type', $type, $object, $this );
		};

		/**
		 * Filter the possible_types to allow systems to add to the possible resolveTypes.
		 *
		 * @param $types array The possible types for the Union
		 * @param $config array The config for the Union Type
		 *
		 * @return array
		 */
		$config['types'] = apply_filters( 'graphql_union_possible_types', $config['types'], $config );

		/**
		 * Filter the config of WPUnionType
		 *
		 * @param array       $config Array of configuration options passed to the WPUnionType when instantiating a new type
		 * @param WPUnionType $this   The instance of the WPObjectType class
		 *
		 * @since 0.0.30
		 */
		$config = apply_filters( 'graphql_wp_union_type_config', $config, $this );

		/**
		 * Run an action when the WPObjectType is instantiating
		 *
		 * @param array       $config Array of configuration options passed to the WPUnionType when instantiating a new type
		 * @param WPUnionType $this   The instance of the WPObjectType class
		 */
		do_action( 'graphql_wp_union_type', $config, $this );

		parent::__construct( $config );
	}
}