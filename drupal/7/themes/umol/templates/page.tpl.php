<?php 
  $tabs = ($tabs = render($tabs)) ? '<div class="tabs">' . $tabs . '</div>' : ''; 

  $show_title = true;
  $show_breadcrumb = false;
  $is_landing_page = false;
  $header_class = '';
  
  $umol_phone = array(
    'data' => '8776986277',
    'label' => '877-MY-UMASS' 
  );
  
  $show_header = true;
  $show_nav = true;
  $show_menu = true;
  $show_footer = true;
  
  $uri = request_uri();
  
  if (strpos($uri, 'jetsense') !== FALSE) {
    $show_header = false;
    $show_nav = false;
    $show_menu = false;
    $show_footer = false;
  }
  
  if (isset($node)) {
    if ($node->type == 'landing_page') {
      $nid = $node->nid;
      $show_title = false;
      $show_breadcrumb = false;
      $is_landing_page = true;
      $header_class = 'is-landing-page';
    }
    
    /* TEMP: show test phone # on a certain landing page */
    if ($node->nid == 10577) {
      $umol_phone['data'] = '8666117656';
      $umol_phone['label'] = '866-611-7656';
    }
  }
?>
<?php print render($page['header']); ?>
<div id="header" class="<?php print $header_class; ?>">
  <div id="header-container">
    <div class="wrapper-960 header">
      <div class="header-logo">
        <a href="/"><img src="/sites/all/themes/umol/images/umassonline-tm-logo.png" alt="UMassOnline Logo" /></a>
      </div>
      <?php if (!$is_landing_page && $show_header == true): ?>
        <div class="header-menu-btn"><a href="#site-menu" class="scroll" title="Menu"></a></div>
        <div class="header-cta">
          <div class="link-row">
            <a href="/lets-get-social">Let's Get Social</a><a href="/blog">Blog</a><a href="/request-info">Request Info</a><a href="tel:<?php print $umol_phone['data']; ?>"><?php print $umol_phone['label']; ?></a>
          </div>
          <div class="search margin-top-5">
            <?php if($page['search']) { print render($page['search']); } ?>
          </div>
        </div>
      <?php endif; ?>
    </div>
  </div>
  <?php if ($show_nav): ?>
    <div id="nav-container">
      <div class="wrapper-960 nav">
        <div id="navigation">
          <div class="section clearfix">
            <ul>
              <li><a href="/degrees-and-certificates">Online Programs</a></li>
              <li><a href="/course-browse">Online Courses</a></li>
              <li><a href="/course-demo">Course Demo</a></li>
              <li><a href="/about-us">About Us</a></li>
              <li><a href="/student-services">Student Success</a></li>
              <li><a href="/request-info" class="hilite">Apply Now</a></li>
              <li><a href="https://umol.umassonline.net">Course Login</a></li>
            </ul>
          </div>
        </div>
      </div>
    </div>
  <?php endif; ?>
</div>
<div id="header-spacer" class="<?php print $header_class; ?>"></div>
<div id="messages"><?php print $messages; ?></div>
<div id="main" class="main">	
  <?php if($page['splash']): ?>
    <?php print render($page['splash']); ?>
    <div style="display:none"><?php print render($page['content']); ?></div>
  <?php elseif($page['hero']): ?>
    <?php print render($page['hero']); ?>    
  <?php else: ?>
    <?php if ($is_landing_page): ?>
      <?php print umol_get_landing_page_header($node); ?>
    <?php else : ?>
      <div id="headline" class="padding-ver-25">
        <?php print '<h1>' . $title . '</h1>'; ?>
      </div>
    <?php endif; ?>

    <?php if (isset($node)): ?>
      <?php print umol_get_campus_header($node); ?>
    <?php endif; ?>
    
    <?php if($page['first_white']): ?>
      <?php print render($page['first_white']); ?>    
    <?php elseif($page['content']): ?>
      <?php if($page['content_sidebar']): ?>
        <div id="content-2col" class="wrapper-960 padding-ver-25">
          <div id="content">
            <?php print $tabs; ?>
            <?php print render($page['content']); ?>
            <div class="clear"></div>
          </div>
        
          <div id="content_sidebar">
            <?php print render($page['content_sidebar']); ?>
            <div class="clear"></div>          
          </div>
        </div>
        <div class="clear"></div>
      <?php else: ?>
        <?php if ($is_landing_page): ?>
          <div id="content" class="wrapper-960">
            <?php print $tabs; ?>
            <?php print render($page['content']); ?>
            <div class="clear"></div>
          </div>
        <?php else : ?>
          <div id="content" class="wrapper-960 padding-top-25 padding-bot-150">
            <?php print $tabs; ?>
            <?php print render($page['content']); ?>
            <div class="clear"></div>
          </div>
        <?php endif; ?>
      <?php endif; ?>
    <?php endif; ?>
  <?php endif; ?>      
  
  <?php     
    if($page['first_grey']) {
      print render($page['first_grey']);
    }
    if($page['second_white']) {
      print render($page['second_white']);
    }
    if($page['second_grey']) {
      print render($page['second_grey']);
    }
    if($page['third_white']) {
      print render($page['third_white']);
    } 
    if($page['third_grey']) {
      print render($page['third_grey']);
    }
    if ($show_menu) { 
      if($page['menu']) {
        print render($page['menu']);
      }
    }
    if ($show_footer) {
      if($page['footer']) {
        print render($page['footer']);
      }
    }
  ?>	
  
  <?php if ($page['help']): ?>
    <div id="help">
      <?php print render($page['help']); ?>
    </div>
  <?php endif; ?>
  
  <?php if ($action_links): ?>
    <ul class="action-links">
      <?php print render($action_links); ?>
    </ul>
  <?php endif; ?>
  
  <?php print render($primary_local_tasks); ?>
</div>

<div id="modal"></div>

<div id="loader" style="display:none"><img src="/sites/all/themes/umol/images/loader.gif" /></div>