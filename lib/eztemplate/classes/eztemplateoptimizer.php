<?php
//
// Definition of eZTemplateOptimizer class
//
// Created on: <16-Aug-2004 15:02:51 dr>
//
// Copyright (C) 1999-2004 eZ systems as. All rights reserved.
//
// This source file is part of the eZ publish (tm) Open Source Content
// Management System.
//
// This file may be distributed and/or modified under the terms of the
// "GNU General Public License" version 2 as published by the Free
// Software Foundation and appearing in the file LICENSE included in
// the packaging of this file.
//
// Licencees holding a valid "eZ publish professional licence" version 2
// may use this file in accordance with the "eZ publish professional licence"
// version 2 Agreement provided with the Software.
//
// This file is provided AS IS with NO WARRANTY OF ANY KIND, INCLUDING
// THE WARRANTY OF DESIGN, MERCHANTABILITY AND FITNESS FOR A PARTICULAR
// PURPOSE.
//
// The "eZ publish professional licence" version 2 is available at
// http://ez.no/ez_publish/licences/professional/ and in the file
// PROFESSIONAL_LICENCE included in the packaging of this file.
// For pricing of this licence please contact us via e-mail to licence@ez.no.
// Further contact information is available at http://ez.no/company/contact/.
//
// The "GNU General Public License" (GPL) is available at
// http://www.gnu.org/copyleft/gpl.html.
//
// Contact licence@ez.no if any conditions of this licencing isn't clear to
// you.
//

/*! \file eztemplateoptimizer.php
*/

/*!
  \class eZTemplateOptimizer eztemplateoptimizer.php
  \brief Analyses a compiled template tree and tries to optimize certain parts of it.

*/

include_once( 'lib/ezutils/classes/ezdebug.php' );
include_once( 'lib/eztemplate/classes/eztemplate.php' );

class eZTemplateOptimizer
{
    /*!
     Constructor
    */
    function eZTemplateOptimizer()
    {
    }

    /*!
     Analyses function nodes and tries to optimize them
    */
    function optimizeFunction( $useComments, &$php, &$tpl, &$node, &$resourceData )
    {
        /* Just run the optimizer over all parameters */
        if ( isset( $node[3] ) and is_array( $node[3] ) )
        {
            foreach ( $node[3] as $key => $parameter )
            {
                eZTemplateOptimizer::optimizeVariable( $useComments, $php, $tpl, $node[3][$key], $resourceData );
            }
        }
    }

    /*!
     Analyses variables and tries to optimize them
    */
    function optimizeVariable( $useComments, &$php, &$tpl, &$data, &$resourceData )
    {
        /* node.object.data_map optimization */
        if ( ( count( $data ) > 3 ) and 
             ( $data[0][0] == 4 ) and
             ( $data[0][1][2] == 'node' ) and
             ( $data[1][0] == 5 ) and
             ( $data[1][1][0][1] == 'object' ) and
             ( $data[2][0] == 5 ) and
             ( $data[2][1][0][1] == 'data_map' ) )
        {

            unset($data[1], $data[2]);
            $data[0] = array( EZ_TEMPLATE_TYPE_OPTIMIZED_NODE, null, 2 );
        }

        /* node.object.data_map optimization through function */
        if ( $data[0][0] == 101 )
        {
            eZTemplateOptimizer::optimizeFunction( $useComments, $php, $tpl, $data[0], $resourceData );
        }
    }

    /*!
     Runs the optimizer
    */
    function optimize( $useComments, &$php, &$tpl, &$tree, &$resourceData )
    {
        /* Loop through the children of the root */
        foreach ( $tree[1] as $key => $kiddie )
        {
            /* Analyse per node type */
            switch ( $kiddie[0] )
            {
                case 3: /* Variable */
                    eZTemplateOptimizer::optimizeVariable( $useComments, $php, $tpl, $tree[1][$key][2], $resourceData );
                    break;
            }
        }
    }
}
