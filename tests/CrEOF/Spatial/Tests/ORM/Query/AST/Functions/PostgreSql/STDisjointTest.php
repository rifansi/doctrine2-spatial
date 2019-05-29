<?php
/**
 * Copyright (C) 2012 Derek J. Lambert
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
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

namespace CrEOF\Spatial\Tests\ORM\Functions\PostgreSql;

use CrEOF\Spatial\PHP\Types\Geometry\LineString;
use CrEOF\Spatial\PHP\Types\Geometry\Point;
use CrEOF\Spatial\PHP\Types\Geometry\Polygon;
use CrEOF\Spatial\Tests\Fixtures\PolygonEntity;
use CrEOF\Spatial\Tests\OrmTest;
use Doctrine\ORM\Query;

/**
 * ST_Disjoint DQL function tests
 *
 * @author  Derek J. Lambert <dlambert@dereklambert.com>
 * @license http://dlambert.mit-license.org MIT
 *
 * @group postgresql
 * @group dql
 */
class STDisjointTest extends OrmTest
{
    protected function setUp()
    {
        $this->useEntity('polygon');
        parent::setUp();
    }

    /**
     * @group geometry
     */
    public function testSelectSTDisjoint()
    {
        $lineString1 = new LineString(array(
            new Point(0, 0),
            new Point(10, 0),
            new Point(10, 10),
            new Point(0, 10),
            new Point(0, 0)
        ));
        $lineString2 = new LineString(array(
            new Point(5, 5),
            new Point(7, 5),
            new Point(7, 7),
            new Point(5, 7),
            new Point(5, 5)
        ));
        $lineString3 = new LineString(array(
            new Point(15, 15),
            new Point(17, 15),
            new Point(17, 17),
            new Point(15, 17),
            new Point(15, 15)
        ));

        $entity1 = new PolygonEntity();

        $entity1->setPolygon(new Polygon(array($lineString1)));
        $this->_em->persist($entity1);

        $entity2 = new PolygonEntity();

        $entity2->setPolygon(new Polygon(array($lineString2)));
        $this->_em->persist($entity2);
        $this->_em->flush();

        $entity3 = new PolygonEntity();

        $entity3->setPolygon(new Polygon(array($lineString3)));
        $this->_em->persist($entity3);
        $this->_em->flush();
        $this->_em->clear();

        $query = $this->_em->createQuery('SELECT p, ST_Disjoint(p.polygon, ST_GeomFromText(:p1)) FROM CrEOF\Spatial\Tests\Fixtures\PolygonEntity p');

        $query->setParameter('p1', new Polygon(array($lineString2)), 'polygon');

        $result = $query->getResult();

        $this->assertCount(3, $result);
        $this->assertEquals($entity1, $result[0][0]);
        $this->assertFalse($result[0][1]);
        $this->assertEquals($entity2, $result[1][0]);
        $this->assertFalse($result[1][1]);
        $this->assertEquals($entity3, $result[2][0]);
        $this->assertTrue($result[2][1]);
    }

    /**
     * @group geometry
     */
    public function testSTDisjointWhereParameter()
    {
        $lineString1 = new LineString(array(
            new Point(0, 0),
            new Point(10, 0),
            new Point(10, 10),
            new Point(0, 10),
            new Point(0, 0)
        ));
        $lineString2 = new LineString(array(
            new Point(5, 5),
            new Point(7, 5),
            new Point(7, 7),
            new Point(5, 7),
            new Point(5, 5)
        ));
        $lineString3 = new LineString(array(
            new Point(15, 15),
            new Point(17, 15),
            new Point(17, 17),
            new Point(15, 17),
            new Point(15, 15)
        ));

        $entity1 = new PolygonEntity();

        $entity1->setPolygon(new Polygon(array($lineString1)));
        $this->_em->persist($entity1);

        $entity2 = new PolygonEntity();

        $entity2->setPolygon(new Polygon(array($lineString2)));
        $this->_em->persist($entity2);
        $this->_em->flush();

        $entity3 = new PolygonEntity();

        $entity3->setPolygon(new Polygon(array($lineString3)));
        $this->_em->persist($entity3);
        $this->_em->flush();
        $this->_em->clear();

        $query = $this->_em->createQuery('SELECT p FROM CrEOF\Spatial\Tests\Fixtures\PolygonEntity p WHERE ST_Disjoint(p.polygon, ST_GeomFromText(:p1)) = true');

        $query->setParameter('p1', new Polygon(array($lineString2)), 'polygon');

        $result = $query->getResult();

        $this->assertCount(1, $result);
        $this->assertEquals($entity3, $result[0]);
        $this->_em->clear();

        $query = $this->_em->createQuery('SELECT p FROM CrEOF\Spatial\Tests\Fixtures\PolygonEntity p WHERE ST_Disjoint(p.polygon, ST_GeomFromText(:p1)) = true');

        $query->setParameter('p1', new Polygon(array($lineString3)), 'polygon');

        $result = $query->getResult();

        $this->assertCount(2, $result);
        $this->assertEquals($entity1, $result[0]);
        $this->assertEquals($entity2, $result[1]);
    }
}
