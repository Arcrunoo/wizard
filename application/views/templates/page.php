<h1 class="page_title">
	<?= $page['title'] ?>
</h1>

<? if ($page['content']): ?>
	<div class="page_content">
		<?= $page['content'] ?>
	</div>	
<? endif ?>
<? if ($page['html_content']): ?>
	<div class="page_content">
		<?= $page['html_content'] ?>
	</div>	
<? endif ?>

<? if ($page['childrens']): ?>
	<ul class="page_childrens childrens_underpage">
		<? foreach ($page['childrens'] as $children): ?>
			<li><a href="/page/<?= $children['uri'] ? $children['uri'] : $children['id'] ?>"><?= $children['title'] ?></a></li>
		<? endforeach ?>
	</ul>
<? endif; ?>

<a class="button green" href="/page/add?type=<?= Controller_Pages::TYPE_PAGE ?>&parent=<?= $page['id'] ?>">Добавить страницу</a>
<? if (isset($files) && $files): ?>
	<table class="page_files inpage">
		<? foreach ($files as $file): ?>
			<tr>
				<td class="ext"><span class="ext_tag"><?= $file['extension'] ?></span></td>
				<td class="title"><?= $file['title'] ?></td>
				<td>
					<p class="size"><?= (int)$file['size'] < 1000 ? $file['size'] . PHP_EOL . 'КБ' : ceil($file['size'] / 1000) . PHP_EOL . 'МБ' ?></p>
				</td>
			</tr>
		<? endforeach ?>
	</table>
<? endif; ?>
<div class="page_comments">
	Комментировать
	<form action="/addcomment" class="add_comment_form mt20">
		<textarea name="text" rows="6"></textarea>
	</form>
</div>

