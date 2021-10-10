<?php
/**
 * @license MIT
 * Copyright 2017, Dustin Wilson, J. King et al.
 * See LICENSE and AUTHORS files for details
 */

declare(strict_types=1);
namespace MensBeam\HTML\DOM;


trait ChildNode {
    public function after(...$nodes): void {
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
