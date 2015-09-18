<?php
/*
Copyright (c) 2012, University of Cambridge Computing Service

This file is part of the Lookup/Ibis client library.

This library is free software: you can redistribute it and/or modify
it under the terms of the GNU Lesser General Public License as published
by the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This library is distributed in the hope that it will be useful, but
WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY
or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU Lesser General Public
License for more details.

You should have received a copy of the GNU Lesser General Public License
along with this library.  If not, see <http://www.gnu.org/licenses/>.
*/

require_once "IbisContactRow.php";
require_once "IbisDto.php";
require_once "IbisGroup.php";
require_once "IbisPerson.php";

/**
 * Class representing an institution returned by the web service API.
 *
 * @author Dean Rasheed (dev-group@ucs.cam.ac.uk)
 */
class IbisInstitution extends IbisDto
{
    /* Properties marked as @XmlAttribte in the JAXB class */
    protected static $xmlAttrs = array("cancelled", "instid", "id", "ref");

    /* Properties marked as @XmlElement in the JAXB class */
    protected static $xmlElems = array("name", "acronym");

    /* Properties marked as @XmlElementWrapper in the JAXB class */
    protected static $xmlArrays = array("attributes", "contactRows", "members",
                                        "parentInsts", "childInsts", "groups",
                                        "membersGroups", "managedByGroups");

    /** @var boolean Flag indicating if the institution is cancelled. */
    public $cancelled;

    /** @var string The institution's unique ID (e.g., "CS"). */
    public $instid;

    /** @var string The institution's name. */
    public $name;

    /** @var string The institutions's acronym, if set (e.g., "UCS"). */
    public $acronym;

    /**
     * @var IbisAttribute[] A list of the institution's attributes. This will
     * only be populated if the ``fetch`` parameter includes the
     * ``"all_attrs"`` option, or any specific attribute schemes such as
     * ``"email"`` or ``"address"``, or the special pseudo-attribute scheme
     * ``"phone_numbers"``.
     */
    public $attributes;

    /**
     * @var IbisContactRow[] A list of the institution's contact rows. This
     * will only be populated if the ``fetch`` parameter includes the
     * ``"contact_rows"`` option.
     */
    public $contactRows;

    /**
     * @var IbisPerson[] A list of the institution's members. This will only
     * be populated if the ``fetch`` parameter includes the ``"all_members"``
     * option.
     */
    public $members;

    /**
     * @var IbisInstitution[] A list of the institution's parent
     * institutions. This will only be populated if the ``fetch`` parameter
     * includes the ``"parent_insts"`` option.
     *
     * NOTE: Currently all institutions have one parent, but in the future
     * institutions may have multiple parents.
     */
    public $parentInsts;

    /**
     * @var IbisInstitution[] A list of the institution's child institutions.
     * This will only be populated if the ``fetch`` parameter includes the
     * ``"child_insts"`` option.
     */
    public $childInsts;

    /**
     * @var IbisGroup[] A list of all the groups that belong to the
     * institution. This will only be populated if the ``fetch`` parameter
     * includes the ``"inst_groups"`` option.
     */
    public $groups;

    /**
     * @var IbisGroup[] A list of the groups that form the institution's
     * membership. This will only be populated if the ``fetch`` parameter
     * includes the ``"members_groups"`` option.
     */
    public $membersGroups;

    /**
     * @var IbisGroup[] A list of the groups that manage this institution.
     * This will only be populated if the ``fetch`` parameter includes the
     * ``"managed_by_groups"`` option.
     */
    public $managedByGroups;

    /**
     * @ignore
     * @var string An ID that can uniquely identify this institution within
     * the returned XML/JSON document. This is only used in the flattened
     * XML/JSON representation (if the "flatten" parameter is specified).
     */
    public $id;

    /**
     * @ignore
     * @var string A reference (by id) to an institution element in the
     * XML/JSON document. This is only used in the flattened XML/JSON
     * representation (if the "flatten" parameter is specified).
     */
    public $ref;

    /* Flag to prevent infinite recursion due to circular references. */
    private $unflattened;

    /**
     * @ignore
     * Create an IbisInstitution from the attributes of an XML node.
     *
     * @param array $attrs The attributes on the XML node.
     */
    public function __construct($attrs=array())
    {
        parent::__construct($attrs);
        if (isset($this->cancelled))
            $this->cancelled = strcasecmp($this->cancelled, "true") == 0;
        $this->unflattened = false;
    }

    /**
     * @ignore
     * Unflatten a single IbisInstitution.
     *
     * @param IbisResultEntityMap $em The mapping from IDs to entities.
     */
    public function unflatten($em)
    {
        if (isset($this->ref))
        {
            $inst = $em->getInstitution($this->ref);
            if (!$inst->unflattened)
            {
                $inst->unflattened = true;
                IbisContactRow::unflattenContactRows($em, $inst->contactRows);
                IbisPerson::unflattenPeople($em, $inst->members);
                IbisInstitution::unflattenInsts($em, $inst->parentInsts);
                IbisInstitution::unflattenInsts($em, $inst->childInsts);
                IbisGroup::unflattenGroups($em, $inst->groups);
                IbisGroup::unflattenGroups($em, $inst->membersGroups);
                IbisGroup::unflattenGroups($em, $inst->managedByGroups);
            }
            return $inst;
        }
        return $this;
    }

    /**
     * @ignore
     * Unflatten a list of IbisInstitution objects (done in place).
     *
     * @param IbisResultEntityMap $em The mapping from IDs to entities.
     * @param IbisInstitution[] $insts The institutions to unflatten.
     */
    public static function unflattenInsts($em, &$insts)
    {
        if (isset($insts))
            foreach ($insts as $idx => $inst)
                $insts[$idx] = $inst->unflatten($em);
    }
}
