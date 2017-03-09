<?php
/**
 * Copyright (c) Andreas Heigl<andreas@heigl.org>
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * @author    Andreas Heigl<andreas@heigl.org>
 * @copyright Andreas Heigl
 * @license   http://www.opensource.org/licenses/mit-license.php MIT-License
 * @since     09.03.2017
 * @link      http://github.com/heiglandreas/org.heigl.Holidaychecker
 */

namespace Org_Heigl\Holidaychecker;

use Org_Heigl\Holidaychecker\IteratorItem\Date;
use Org_Heigl\Holidaychecker\IteratorItem\Easter;
use Org_Heigl\Holidaychecker\IteratorItem\Relative;

class HolidayIteratorFactory
{
    public function createIteratorFromXmlFile(string $file) : HolidayIterator
    {
        $iterator = new HolidayIterator();

        $dom = new \DOMDocument('1.0', 'UTF-8');
        $dom->load($file);
        if (! $dom->schemaValidate(__DIR__ . '/../share/holidays.xsd')) {
            throw new \Exception('XML-File does not validate agains schema');
        }
        foreach ($dom->documentElement->childNodes as $child) {
            if (! $child instanceof \DOMElement) {
                continue;
            }
            $iterator->append($this->getElement($child));
            /** @var \DOMElement $child */

        }

        return $iterator;
    }

    private function getElement(\DOMElement $child) : HolidayIteratorItemInterface
    {
        switch ($child->nodeName) {
            case 'easter':
                return new Easter(
                    $child->textContent,
                    $this->getFree($child),
                    $child->getAttribute('offset')
                );
            case 'date':
                return new Date(
                    $child->textContent,
                    $this->getFree($child),
                    $child->getAttribute('day'),
                    $child->getAttribute('month'),
                    ($child->hasAttribute('year')?$child->getAttribute('year'): null)
                );
            case 'relative':
                return new Relative(
                    $child->textContent,
                    $this->getFree($child),
                    $child->getAttribute('day'),
                    $child->getAttribute('month'),
                    $child->getAttribute('relation')
                );
        }
    }

    private function getFree(\DOMElement $element)
    {
        return ($element->getAttribute('free') === "true");
    }
}
