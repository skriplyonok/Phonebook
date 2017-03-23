<?php
return array(
    
    'doctrine' => array(
        'driver' => array(
            // defines an annotation driver with two paths, and names it `my_annotation_driver`
            'book_entity' => array(
                'class' => 'Doctrine\ORM\Mapping\Driver\AnnotationDriver',
                'cache' => 'array',
                'paths' => array(
                    __DIR__ . '/../src/Book/Entity',
                ),
            ),

            // default metadata driver, aggregates all other drivers into a single one.
            // Override `orm_default` only if you know what you're doing
            'orm_default' => array(
                'drivers' => array(
                    // register `my_annotation_driver` for any entity under namespace `My\Namespace`
                    'Book\Entity' => 'book_entity'
                )
            )
        )
    ),
    
    'controllers' => array(
        'invokables' => array(
            'Book\Controller\Index' => 'Book\Controller\IndexController',
            'contact' => 'Book\Controller\ContactController',
        ),
    ),
    
    'router' => array(
        'routes' => array(
            'book' => array(
                'type'    => 'literal',
                'options' => array(
                    'route'    => '/book/',
                    'defaults' => array(
                        'controller' => 'Book\Controller\Index',
                        'action'     => 'index',
                    ),
                ),
                'may_terminate' => true,
                'child_routes' => array(
                    'contact' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route' => 'contact/[:action/][:id/]',
                            'defaults' => array(
                                'controller' => 'contact',
                                'action' => 'index'
                            )
                        )
                    )
                ),//< child_routes
            ),
        ),
    ),
    
    'view_manager' => array(
        'template_path_stack' => array(
            __DIR__ . '/../view',
        ),
    ),
);