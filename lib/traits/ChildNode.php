<?php
/**
 * @license MIT
 * Copyright 2017, Dustin Wilson, J. King et al.
 * See LICENSE and AUTHORS files for details
 */

declare(strict_types=1);
namespace MensBeam\HTML\DOM;


# 4.2.8. Mixin ChildNode
trait ChildNode {
    public function after(...$nodes): void {
        // PHP's declaration for \DOMCharacterData::after doesn't include the
        // DOMNode|string typing for the nodes that it should, so type checking will
        // need to be done manually.
        foreach ($nodes as $node) {
            if (!$node instanceof \DOMNode && !is_string($node)) {
                $type = gettype($node);
                if ($type === 'object') {
                    $type = get_class($node);
                }
                throw new Exception(Exception::ARGUMENT_TYPE_ERROR, 1, 'nodes', '\DOMNode|string', $type);
            }
        }

        # The after(nodes) method steps are:
        #
        # 1. Let parent be this’s parent.
        $parent = $this->parentNode;

        # 2. If parent is null, then return.
        if ($parent === null) {
            return;
        }

        # 3. Let viableNextSibling be this’s first following sibling not in nodes;
        #    otherwise null.
        $n = $this;
        $viableNextSibling = null;
        while ($n = $n->nextSibling) {
            foreach ($nodes as $nodeOrString) {
                if ($nodeOrString instanceof \DOMNode && $nodeOrString->isSameNode($n)) {
                    continue 2;
                }
            }

            $viableNextSibling = $n;
            break;
        }

        # 4. Let node be the result of converting nodes into a node, given nodes and this’s
        #    node document.
        $node = $this->convertNodesToNode($nodes);

        # 5. Pre-insert node into parent before viableNextSibling.
        $parent->insertBefore($node, $viableNextSibling);
    }
}
