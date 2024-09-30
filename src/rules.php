<?php

return [
    // Each line of multi-line DocComments must have an asterisk [PSR-5] and must be aligned with the first one.
    'align_multiline_comment'                       => [ 'comment_type' => 'all_multiline' ],
    // Each element of an array must be indented exactly once.
    'array_indentation'                             => true,
    // PHP arrays should be declared using the configured syntax.
    'array_syntax'                                  => true,
    'trim_array_spaces'                             => true,
    // Use the null coalescing assignment operator `??=` where possible.
    'assign_null_coalescing_to_coalesce_equal'      => true,
    // PHP attributes declared without arguments must (not) be followed by empty parentheses.
    'attribute_empty_parentheses'                   => true,
    // Binary operators should be surrounded by space as configured.
    'binary_operator_spaces'                        => [ 'default' => 'align_single_space_minimal_by_scope' ],
    // There MUST be one blank line after the namespace declaration.
    'blank_line_after_namespace'                    => true,
    // Ensure there is no code on the same line as the PHP open tag and it is followed by a blank line.
    'blank_line_after_opening_tag'                  => true,
    // An empty line feed must precede any configured statement.
    'blank_line_before_statement'                   => [
            'statements' => [
                    'break',
                    'continue',
                    'declare',
                    'exit',
                    'foreach',
                    // 'return',
            ],
    ],
    // Controls blank lines before a namespace declaration.
    'blank_lines_before_namespace'                  => [ 'min_line_breaks' => 1 ],
    // Braces must be placed as configured.
    'braces_position'                               => true,
    // A single space or none should be between cast and variable.
    'cast_spaces'                                   => true,
    // Class, trait and interface elements must be separated with one or none blank line.
    'class_attributes_separation'                   => true,
    // Whitespace around the keywords of a class, trait, enum or interfaces definition should be one space.
    'class_definition'                              => true,
    // When referencing an internal class it must be written using the correct casing.
    'class_reference_name_casing'                   => true,
    // Namespace must not contain spacing, comments or PHPDoc.
    'clean_namespace'                               => true,
    // Using `isset($var) &&` multiple times should be done in one call.
    'combine_consecutive_issets'                    => true,
    // Calling `unset` on multiple items should be done in one call.
    'combine_consecutive_unsets'                    => true,
    // Remove extra spaces in a nullable type declaration.
    'compact_nullable_type_declaration'             => true,
    // Concatenation should be spaced according to configuration.
    'concat_space'                                  => true,
    // The PHP constants `true`, `false`, and `null` MUST be written using the correct casing.
    'constant_case'                                 => true,
    // The body of each control structure MUST be enclosed within braces.
    'control_structure_braces'                      => true,
    // Control structure continuation keyword must be on the configured line.
    'control_structure_continuation_position'       => [ 'position' => 'next_line' ],
    // Equal sign in declare statement should be surrounded by spaces or not following configuration.
    'declare_equal_normalize'                       => true,
    // There must not be spaces around `declare` statement parentheses.
    'declare_parentheses'                           => true,
    // Doctrine annotations must use configured operator for assignment in arrays.
    'doctrine_annotation_array_assignment'          => true,
    // Doctrine annotations without arguments must use the configured syntax.
    'doctrine_annotation_braces'                    => true,
    // Doctrine annotations must be indented with four spaces.
    'doctrine_annotation_indentation'               => true,
    // Fixes spaces in Doctrine annotations.
    'doctrine_annotation_spaces'                    => true,
    // Replaces short-echo `<?=` with long format `<?php echo`/`<?php print` syntax, or vice-versa.
    'echo_tag_syntax'                               => true,
    // The keyword `elseif` should be used instead of `else if` so that all control keywords look like single words.
    'elseif'                                        => true,
    // Empty loop-body must be in configured style.
    'empty_loop_body'                               => [ 'style' => 'braces' ],
    // Empty loop-condition must be in configured style.
    'empty_loop_condition'                          => true,
    // PHP code MUST use only UTF-8 without BOM (remove BOM).
    'encoding'                                      => true,
    // Add curly braces to indirect variables to make them clear to understand. Requires PHP >= 7.0.
    'explicit_indirect_variable'                    => true,
    // Converts implicit variables into explicit ones in double-quoted strings or heredoc syntax.
    'explicit_string_variable'                      => true,
    // Internal classes should be `final`.
    'final_internal_class'                          => true,
    // PHP code must use the long `<?php` tags or short-echo `<?=` tags and not other tag variations.
    'full_opening_tag'                              => true,
    // Removes the leading part of fully qualified symbol references if a given symbol is imported or belongs to the current namespace.
    'fully_qualified_strict_types'                  => [ 'leading_backslash_in_global_namespace' => true ],
    // Spaces should be properly placed in a function declaration.
    'function_declaration'                          => [
            'closure_fn_spacing'       => 'none',
            'closure_function_spacing' => 'none',
    ],
    // Replace `get_class` calls on object variables with class keyword syntax.
    'get_class_to_class_keyword'                    => true,
    // Imports or fully qualifies global classes/functions/constants.
    'global_namespace_import'                       => [
            'import_classes'   => false,
            'import_constants' => false,
            'import_functions' => false,
    ],
    // There MUST be group use for the same namespaces.
    'group_import'                                  => true,
    // Add, replace or remove header comment.
    // 'header_comment'                                => [
    //     'comment_type' => 'PHPDoc',
    //     'header'       => 'Proper header Content string',
    //     'location'     => 'after_open',
    // ],
    // Heredoc/nowdoc content must be properly indented.
    'heredoc_indentation'                           => true,
    // Convert `heredoc` to `nowdoc` where possible.
    'heredoc_to_nowdoc'                             => true,
    // Include/Require and file path should be divided with a single space. File path should not be placed within parentheses.
    'include'                                       => true,
    // Pre- or post-increment and decrement operators should be used if possible.
    'increment_style'                               => [ 'style' => 'post' ],
    // Code MUST use configured indentation type.
    'indentation_type'                              => true,
    // Integer literals must be in correct case.
    'integer_literal_case'                          => true,
    // All PHP files must use same line ending.
    'line_ending'                                   => true,
    // Ensure there is no code on the same line as the PHP open tag.
    'linebreak_after_opening_tag'                   => true,
    // List (`array` destructuring) assignment should be declared using the configured syntax. Requires PHP >= 7.1.
    'list_syntax'                                   => true,
    // Cast should be written in lower case.
    'lowercase_cast'                                => true,
    // PHP keywords MUST be in lower case.
    'lowercase_keywords'                            => true,
    // Class static references `self`, `static` and `parent` MUST be in lower case.
    'lowercase_static_reference'                    => true,
    // Magic constants should be referred to using the correct casing.
    'magic_constant_casing'                         => true,
    // Magic method definitions and calls must be using the correct casing.
    'magic_method_casing'                           => true,
    // In method arguments and method call, there MUST NOT be a space before each comma and there MUST be one space after each comma. Argument lists MAY be split across multiple lines, where each subsequent line is indented once. When doing so, the first item in the list MUST be on the next line, and there MUST be only one argument per line.
    'method_argument_space'                         => [
            'attribute_placement'              => 'same_line',
            'keep_multiple_spaces_after_comma' => false,
    ],
    // Method chaining MUST be properly indented. Method chaining with different levels of indentation is not supported.
    'method_chaining_indentation'                   => true,
    // Convert multiline string to `heredoc` or `nowdoc`.
    'multiline_string_to_heredoc'                   => true,
    // Forbid multi-line whitespace before the closing semicolon or move the semicolon to the new line for chained calls.
    'multiline_whitespace_before_semicolons'        => true,
    // Function defined by PHP should be called using the correct casing.
    'native_function_casing'                        => true,
    // Native type declarations should be used in the correct case.
    'native_type_declaration_casing'                => true,
    // All instances created with `new` keyword must (not) be followed by parentheses.
    'new_with_parentheses'                          => true,
    // Master language constructs shall be used instead of aliases.
    'no_alias_language_construct_call'              => true,
    // Replace control structure alternative syntax to use braces.
    'no_alternative_syntax'                         => true,
    // There should not be a binary flag before strings.
    'no_binary_string'                              => true,
    // There should be no empty lines after class opening brace.
    'no_blank_lines_after_class_opening'            => true,
    // There should not be blank lines between docblock and the documented element.
    'no_blank_lines_after_phpdoc'                   => true,
    // There must be a comment when fall-through is intentional in a non-empty case body.
    'no_break_comment'                              => [ 'comment_text' => ':no break' ],
    // The closing `? >` tag MUST be omitted from files containing only PHP.
    'no_closing_tag'                                => true,
    // Remove useless (semicolon) statements.
    'no_empty_statement'                            => true,
    // Removes extra blank lines and/or blank lines following configuration.
    'no_extra_blank_lines'                          => true,
    // The namespace declaration line shouldn't contain leading whitespace.
    'no_leading_namespace_whitespace'               => true,
    // Operator `=>` should not be surrounded by multi-line whitespaces.
    'no_multiline_whitespace_around_double_arrow'   => true,
    // There must not be more than one statement per line.
    'no_multiple_statements_per_line'               => true,
    // Properties MUST not be explicitly initialized with `null` except when they have a type declaration (PHP 7.4).
    'no_null_property_initialization'               => true,
    // Short cast `bool` using double exclamation mark should not be used.
    'no_short_bool_cast'                            => true,
    // There must be no space around double colons (also called Scope Resolution Operator or Paamayim Nekudotayim).
    'no_space_around_double_colon'                  => true,
    // When making a method or function call, there MUST NOT be a space between the method or function name and the opening parenthesis.
    'no_spaces_after_function_name'                 => true,
    // There MUST NOT be spaces around offset braces.
    'no_spaces_around_offset'                       => [
            'positions' => [
                    'inside',
                    'outside',
            ],
    ],
    // Replaces superfluous `elseif` with `if`.
    'no_superfluous_elseif'                         => true,
    // If a list of values separated by a comma is contained on a single line, then the last item MUST NOT have a trailing comma.
    'no_trailing_comma_in_singleline'               => true,
    // Remove trailing whitespace at the end of non-blank lines.
    'no_trailing_whitespace'                        => true,
    // There MUST be no trailing spaces inside comment or PHPDoc.
    'no_trailing_whitespace_in_comment'             => true,
    // Removes unneeded braces that are superfluous and aren't part of a control structure's body.
    'no_unneeded_braces'                            => true,
    // Removes unneeded parentheses around control statements.
    'no_unneeded_control_parentheses'               => true,
    // Imports should not be aliased as the same name.
    'no_unneeded_import_alias'                      => true,
    // Variables must be set `null` instead of using `(unset)` casting.
    'no_unset_cast'                                 => true,
    // Unused `use` statements must be removed.
    'no_unused_imports'                             => true,
    // There should not be useless concat operations.
    'no_useless_concat_operator'                    => true,
    // There should not be useless `else` cases.
    'no_useless_else'                               => true,
    // There should not be useless Null-safe operator `?->` used.
    'no_useless_nullsafe_operator'                  => true,
    // There should not be an empty `return` statement at the end of a function.
    'no_useless_return'                             => true,
    // In array declaration, there MUST NOT be a whitespace before each comma.
    'no_whitespace_before_comma_in_array'           => true,
    // Remove trailing whitespace at the end of blank lines.
    'no_whitespace_in_blank_line'                   => true,
    // Array index should always be written by using square braces.
    'normalize_index_brace'                         => true,
    // Logical NOT operators (`!`) should have leading and trailing whitespaces.
    'not_operator_with_space'                       => true,
    // Nullable single type declaration should be standardised using configured syntax.
    'nullable_type_declaration'                     => true,
    // Adds separators to numeric literals of any kind.
    'numeric_literal_separator'                     => true,
    // There should not be space before or after object operators `->` and `?->`.
    'object_operator_without_whitespace'            => true,
    // Operators - when multiline - must always be at the beginning or at the end of the line.
    'operator_linebreak'                            => true,
    // PHPDoc should contain `@param` for all params.
    'phpdoc_add_missing_param_annotation'           => [ 'only_untyped' => false ],
    // All items of the given PHPDoc tags must be either left-aligned or (by default) aligned vertically.
    'phpdoc_align'                                  => [ 'align' => 'vertical' ],
    // PHPDoc annotation descriptions should not be a sentence.
    'phpdoc_annotation_without_dot'                 => true,
    // Docblocks should have the same indentation as the documented subject.
    'phpdoc_indent'                                 => true,
    // Fixes PHPDoc inline tags.
    'phpdoc_inline_tag_normalizer'                  => true,
    // Changes doc blocks from single to multi line, or reversed. Works for class constants, properties and methods only.
    'phpdoc_line_span'                              => [
            'const'    => 'single',
            'method'   => 'multi',
            'property' => 'single',
    ],
    // No alias PHPDoc tags should be used.
    'phpdoc_no_alias_tag'                           => true,
    // Classy that does not inherit must not have `@inheritdoc` tags.
    'phpdoc_no_useless_inheritdoc'                  => true,
    // Orders all `@param` annotations in DocBlocks according to method signature.
    'phpdoc_param_order'                            => true,
    // The type of `@return` annotations of methods returning a reference to itself must the configured one.
    'phpdoc_return_self_reference'                  => true,
    // Scalar types should always be written in the same form. `int` not `integer`, `bool` not `boolean`, `float` not `real` or `double`.
    'phpdoc_scalar'                                 => true,
    // Single line `@var` PHPDoc should have proper spacing.
    'phpdoc_single_line_var_spacing'                => true,
    // PHPDoc summary should end in either a full stop, exclamation mark, or question mark.
    'phpdoc_summary'                                => true,
    // Fixes casing of PHPDoc tags.
    'phpdoc_tag_casing'                             => true,
    // Forces PHPDoc tags to be either regular annotations or inline.
    'phpdoc_tag_type'                               => true,
    // Docblocks should only be used on structural elements.
    'phpdoc_to_comment'                             => false,
    // PHPDoc should start and end with content, excluding the very first and last line of the docblocks.
    'phpdoc_trim'                                   => true,
    // Removes extra blank lines after summary and after description in PHPDoc.
    'phpdoc_trim_consecutive_blank_line_separation' => true,
    // The correct case must be used for standard PHP types in PHPDoc.
    'phpdoc_types'                                  => true,
    // Sorts PHPDoc types.
    'phpdoc_types_order'                            => true,
    // `@var` and `@type` annotations must have type and name in the correct order.
    'phpdoc_var_annotation_correct_order'           => true,
    // `@var` and `@type` annotations of classy properties should not contain the name.
    'phpdoc_var_without_name'                       => true,
    // Adjust spacing around colon in return type declarations and backed enum types.
    'return_type_declaration'                       => [ 'space_before' => 'one' ],
    // Inside an enum or `final`/anonymous class, `self` should be preferred over `static`.
    'self_static_accessor'                          => true,
    // Instructions must be terminated with a semicolon.
    'semicolon_after_instruction'                   => true,
    // Cast `(boolean)` and `(integer)` should be written as `(bool)` and `(int)`, `(double)` and `(real)` as `(float)`, `(binary)` as `(string)`.
    'short_scalar_cast'                             => true,
    // Converts explicit variables in double-quoted strings and heredoc syntax from simple to complex format (`${` to `{$`).
    'simple_to_complex_string_variable'             => true,
    // Simplify `if` control structures that return the boolean result of their condition.
    'simplified_if_return'                          => true,
    // A return statement wishing to return `void` should not return `null`.
    'simplified_null_return'                        => true,
    // A PHP file without end tag must always end with a single empty line feed.
    'single_blank_line_at_eof'                      => true,
    // There MUST NOT be more than one property or constant declared per statement.
    // 'single_class_element_per_statement'            => true,
    // There MUST be one use keyword per declaration.
    'single_import_per_statement'                   => false,
    // Single-line comments must have proper spacing.
    'single_line_comment_spacing'                   => true,
    // Single-line comments and multi-line comments with only one line of actual content should use the `//` syntax.
    'single_line_comment_style'                     => true,
    // Empty body of class, interface, trait, enum or function must be abbreviated as `{}` and placed on the same line as the previous symbol, separated by a single space.
    'single_line_empty_body'                        => true,
    // Throwing exception must be done in single line.
    'single_line_throw'                             => true,
    // Convert double quotes to single quotes for simple strings.
    'single_quote'                                  => true,
    // Ensures a single space after language constructs.
    'single_space_around_construct'                 => true,
    // Fix whitespace after a semicolon.
    'space_after_semicolon'                         => true,
    // Parentheses must be declared using the configured whitespace.
    'spaces_inside_parentheses'                     => [ 'space' => 'single' ],
    // Increment and decrement operators should be used if possible.
    'standardize_increment'                         => true,
    // Replace all `<>` with `!=`.
    'standardize_not_equals'                        => true,
    // Each statement must be indented.
    'statement_indentation'                         => true,
    // A case should be followed by a colon and not a semicolon.
    'switch_case_semicolon_to_colon'                => true,
    // Removes extra spaces between colon and case value.
    'switch_case_space'                             => true,
    // Switch case must not be ended with `continue` but with `break`.
    'switch_continue_to_break'                      => true,
    // Standardize spaces around ternary operator.
    'ternary_operator_spaces'                       => true,
    // Use `null` coalescing operator `??` where possible. Requires PHP >= 7.0.
    'ternary_to_null_coalescing'                    => true,
    // Arguments lists, array destructuring lists, arrays that are multi-line, `match`-lines and parameters lists must have a trailing comma.
    'trailing_comma_in_multiline'                   => [
            'elements' => [
                    'arguments',
                    'arrays',
                    'match',
                    'parameters',
            ],
    ],
    // Ensure single space between a variable and its type declaration in function arguments and properties.
    'type_declaration_spaces'                       => true,
    // A single space or none should be around union type and intersection type operators.
    'types_spaces'                                  => true,
    // Unary operators should be placed adjacent to their operands.
    'unary_operator_spaces'                         => true,
    // Visibility MUST be declared on all properties and methods; `abstract` and `final` MUST be declared before the visibility; `static` MUST be declared after the visibility.
    'visibility_required'                           => true,
    // In array declaration, there MUST be a whitespace after each comma.
    'whitespace_after_comma_in_array'               => [ 'ensure_single_space' => true ],
    // Write conditions in Yoda style (`true`), non-Yoda style (`['equal' => false, 'identical' => false, 'less_and_greater' => false]`) or ignore those conditions (`null`) based on configuration.
    'yoda_style'                                    => [ 'always_move_variable' => true ],
];