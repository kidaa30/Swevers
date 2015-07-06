<? 
if ($title) navigation_stack()->add($title);
$stack = navigation_stack()->get_current_stack();

if (count($stack) > 1): ?>
	<nav id="breadcrumbs">
		<? foreach ($stack as $breadcrumb):?>
			<a class="breadcrumb<?=$breadcrumb==end($stack)?' active" onclick="return false;':''?>" href="<?=$breadcrumb['url']?>"><span><?=$breadcrumb['title']?></span></a>
		<? endforeach; ?>
	</nav>
<? endif;