<div id="groups">
    <div class="row">
        <h3><?= $words->get('GroupsSearchHeading'); ?></h3>
        <form action="groups/search" method="GET">
            <input type="text" name="GroupsSearchInput" value="" id="GroupsSearchInput" /><input type="submit" value="<?= $words->get('GroupsSearchSubmit'); ?>" /><br />
        </form>
    </div> <!-- row -->
    
    <div class="row">
        <h3><?= $words->get('GroupsSearchResult'); ?></h3>
        <?php
        if ($this->search_result)
        {
            $name_order = (($this->result_order == "nameasc") ? 'namedesc' : 'nameasc');
            $member_order = (($this->result_order == "membersasc") ? 'membersdesc' : 'membersasc');
            $created_order = (($this->result_order == "createdasc") ? 'createddesc' : 'createdasc');
            $category_order = (($this->result_order == "categoryasc") ? 'categorydesc' : 'categoryasc');
            echo <<<HTML
            <h4>Order by:</h4>
            <p class="grey">
            <a class="grey" href="groups/search?GroupsSearchInput={$this->search_terms}&amp;Order={$name_order}&Page={$this->result_page}">Group name</a>
            |
            <a class="grey" href="groups/search?GroupsSearchInput={$this->search_terms}&amp;Order={$member_order}&Page={$this->result_page}">Number of Members</a>
            |
            <a class="grey" href="groups/search?GroupsSearchInput={$this->search_terms}&amp;Order={$created_order}&Page={$this->result_page}">Date of creation</a>
HTML;
// Categories link disabled until we have categories
//            |
//            <a class="grey" href="groups/search?GroupsSearchInput={$this->search_terms}&amp;Order={$category_order}&Page={$this->result_page}">Category</a>
?>
    </div> <!-- row -->           
<?

            foreach ($this->search_result as $group_data) : ?>
                <div class="groupinfo">
                    <img class="framed float_left"  width="60px" alt="group" src="<?= ((strlen($group_data->Picture) > 0) ? "groups/thumbimg/{$group_data->getPKValue()}" : 'images/icons/group.png' ) ?>"/>
                    <h4><a href="groups/<?=$group_data->id ?>"><?=$group_data->Name ?></a></h4>
                    <ul>
                        <li><?= $words->get('GroupsMemberCount');?>: <?=$group_data->getMemberCount(); ?></li>
                        <li><?= $words->get('GroupsDateCreation');?>: <?=$group_data->created; ?></li>
                        <li><?= $words->get('GroupsNewForumPosts');?>: <?=$group_data->getNewForumPosts; ?></li>
                    </ul>
                </div> <!-- groupinfo -->
            <?php endforeach ; 
        }
        else
        {
            echo <<<HTML
            <div>
            {$words->get('GroupSearchNoResults')}
            </div>
</div>
</div>
HTML;
        }
        ?>
