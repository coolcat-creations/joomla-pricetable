<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_content
 * @author      Elisa Foltyn - coolcat-creations.com
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 *
 * The html structure of this pricetable is based on this codepen https://codepen.io/shamim539/pen/LGWaEZ
 */


use Joomla\CMS\Factory;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;

defined('_JEXEC') or die;

JHtml::addIncludePath(JPATH_COMPONENT . '/helpers');

JHtml::_('behavior.caption');

$dispatcher = JEventDispatcher::getInstance();

$this->category->text = $this->category->description;

$dispatcher->trigger('onContentPrepare', array($this->category->extension . '.categories', &$this->category, &$this->params, 0));
$this->category->description = $this->category->text;

$results = $dispatcher->trigger('onContentAfterTitle', array($this->category->extension . '.categories', &$this->category, &$this->params, 0));
$afterDisplayTitle = trim(implode("\n", $results));

$results = $dispatcher->trigger('onContentBeforeDisplay', array($this->category->extension . '.categories', &$this->category, &$this->params, 0));
$beforeDisplayContent = trim(implode("\n", $results));

$results = $dispatcher->trigger('onContentAfterDisplay', array($this->category->extension . '.categories', &$this->category, &$this->params, 0));
$afterDisplayContent = trim(implode("\n", $results));


/* Heading Semantic */

$heading1 = 'h1';
$heading2 = 'h2';

if ($this->params->get('show_category_title') && !$this->params->get('show_page_heading') || (!$this->params->get('show_category_title') && $this->params->get('show_page_heading'))) {
	$heading2 = 'h1';
}

?>

<div class="pricing-plan <?php echo $this->pageclass_sfx; ?>">

	<?php /* this will get the page heading of the menu item if enabled in the options */ ?>

	<?php if ($this->params->get('show_page_heading')) : ?>
	<div class="section-heading blue-border text-center">
		<<?php echo $heading1; ?> class="letterspace4x">
		<?php echo $this->escape($this->params->get('page_heading')); ?>
	</<?php echo $heading1; ?>>
</div>
<?php endif; ?>

<?php /* this will get the category title of the main category if enabled in the options */ ?>
<?php if ($this->params->get('show_category_title') or $this->params->get('page_subheading')) : ?>
	<div class="section-heading blue-border text-center">
	<<?php echo $heading2; ?>> <?php echo $this->escape($this->params->get('page_subheading')); ?>
	<?php if ($this->params->get('show_category_title')) : ?>
		<span class="subheading-category"><?php echo $this->category->title; ?></span>
	<?php endif; ?>
	</<?php echo $heading2; ?>>
	</div>
<?php endif; ?>

<?php echo $afterDisplayTitle; ?>


<?php /* this will show the category description */ ?>

<?php if ($beforeDisplayContent || $afterDisplayContent || $this->params->get('show_description', 1) || $this->params->def('show_description_image', 1)) : ?>
	<div class="category-desc text-center">
		<?php if ($this->params->get('show_description_image') && $this->category->getParams()->get('image')) : ?>
			<img src="<?php echo $this->category->getParams()->get('image'); ?>"
			     alt="<?php echo htmlspecialchars($this->category->getParams()->get('image_alt'), ENT_COMPAT, 'UTF-8'); ?>"/>
		<?php endif; ?>
		<?php echo $beforeDisplayContent; ?>
		<?php if ($this->params->get('show_description') && $this->category->description) : ?>
			<?php echo JHtml::_('content.prepare', $this->category->description, '', 'com_content.category'); ?>
		<?php endif; ?>
		<?php echo $afterDisplayContent; ?>
	</div>
<?php endif; ?>


<?php /* message if category is empty */ ?>

<?php if (empty($this->lead_items) && empty($this->link_items) && empty($this->intro_items)) : ?>
	<?php if ($this->params->get('show_no_articles', 1)) : ?>
		<p><?php echo JText::_('COM_CONTENT_NO_ARTICLES'); ?></p>
	<?php endif; ?>
<?php endif; ?>


<div class="pricing-content text-center">

	<div class="plan-option">

		<ul class="nav nav-pills">

			<?php

			$firstcat = true;

			$i = 0;
			$len = count($this->children[$this->category->id]);

			foreach ($this->children[$this->category->id] as $category) :
				$last = false;

				if ($i == $len - 1) {
					$last = true;
				}

				?>

				<li class="<?php echo $firstcat ? 'active' : ''; ?>">
					<a data-toggle="tab" href="#<?php echo $this->escape($category->alias); ?>"
					   aria-expanded="true"><?php echo $this->escape($category->title); ?>
					</a>
				</li>

				<?php if (!$last) : ?>
					<li><span class="or-circle">OR</span></li>
				<?php endif; ?>

				<?php
				$firstcat = false;
				$i++;
				?>

			<?php endforeach; ?>
		</ul>
	</div>


	<div class="tab-content">

		<?php


		$firstcat = true; ?>

		<?php foreach ($this->children[$this->category->id] as $category) :	?>

			<div id="<?php echo $this->escape($category->alias); ?>"
			     class="tab-pane fade <?php echo $firstcat ? 'in active' : ''; ?>">
				<div class="row">


					<?php

					$prices = array();

					foreach ($this->lead_items as $item) :

						if (!isset($prices[$item->catid])) :
							$prices[$item->catid] = array();
						endif;

						$prices[$item->catid][] = $item;

					endforeach;

					foreach ($prices[$category->id] as $price) :

						$pricefields = $price->jcfields;

						foreach ($pricefields as $pricefield) {
							$pricefields[$pricefield->name] = $pricefield;
						}
						?>

						<div class="span4">
							<div class="outer-border">
								<div class="pricing-table">
									<div class="upper-detail">
										<span class="h5 plan-name letterspace4x"><?php echo $this->escape($price->title); ?></span>
										<?php echo $price->event->beforeDisplayContent; ?><?php echo $price->introtext; ?><?php echo $price->event->afterDisplayContent; ?>

										<div class="plan-price">
											<span class="h1"><sup><?php echo $pricefields['currency']->value; ?></sup><?php echo $pricefields['price']->value; ?></span>
											<sub><span class="underline"><?php echo $this->escape($category->title); ?></span><span>payment</span></sub>
										</div>
									</div>
									<div class="lower-detail">

										<?php
										$features = json_decode($pricefields['features']->rawvalue);
										?>

										<ul>
											<?php foreach ($features as $feature) : ?>
												<li>
													<span class="<?php echo $feature->iconclass; ?>"></span> <?php echo $feature->feature; ?>
												</li>
											<?php endforeach; ?>

										</ul>


										<?php

										$params = $price->params;

										if ($params->get('show_readmore') && $price->readmore) :
											if ($params->get('access-view')) :
												$link = Route::_(ContentHelperRoute::getArticleRoute($price->slug, $price->catid, $price->language));
											else :
												$menu = Factory::getApplication()->getMenu();
												$active = $menu->getActive();
												$itemId = $active->id;
												$link = new JUri(JRoute::_('index.php?option=com_users&view=login&Itemid=' . $itemId, false));
												$link->setVar('return', base64_encode(ContentHelperRoute::getArticleRoute($price->slug, $price->catid, $price->language)));
											endif; ?>

											<?php echo LayoutHelper::render('joomla.content.readmoreprice', array('item' => $price, 'params' => $params, 'link' => $link));
										endif; ?>

									</div>
								</div>
							</div>
						</div>


					<?php endforeach; ?>
				</div>
			</div>
			<?php $firstcat = false; ?>

		<?php endforeach; ?>
	</div>

</div>
