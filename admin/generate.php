<?php
/**
 * Regenerates the static index.html from content.json.
 * Called automatically on every save.
 */
function generate_site($content) {
    $h = 'htmlspecialchars';
    $c = $content;

    ob_start();
    ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title><?= $h($c['meta']['title']) ?></title>
  <meta name="description" content="<?= $h($c['meta']['description']) ?>" />
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="styles.css" />
  <?php if (!empty($c['hero']['bg_image'])): ?>
  <style>
    .hero { background-image: url('<?= $h($c['hero']['bg_image']) ?>'); background-size: cover; background-position: center; }
    .hero-grid-bg { opacity: 0.5; }
  </style>
  <?php endif; ?>
</head>
<body>

  <nav class="nav" id="nav">
    <div class="nav-inner">
      <a href="#" class="logo">
        <span class="logo-mark"><?= $h(mb_substr($c['nav']['logo_text'], 0, 1)) ?></span>
        <span class="logo-text"><?= $h($c['nav']['logo_text']) ?></span>
      </a>
      <ul class="nav-links">
        <li><a href="#services">Services</a></li>
        <li><a href="#why">Why AI</a></li>
        <li><a href="#process">Process</a></li>
        <li><a href="#results">Results</a></li>
        <li><a href="#contact" class="nav-cta"><?= $h($c['nav']['cta_label']) ?></a></li>
      </ul>
      <button class="hamburger" id="hamburger" aria-label="Menu">
        <span></span><span></span><span></span>
      </button>
    </div>
  </nav>

  <section class="hero" id="hero">
    <div class="hero-grid-bg"></div>
    <div class="hero-glow hero-glow-1"></div>
    <div class="hero-glow hero-glow-2"></div>
    <div class="container hero-inner">
      <div class="hero-badge">
        <span class="badge-dot"></span>
        <?= $h($c['hero']['badge']) ?>
      </div>
      <h1 class="hero-headline">
        <?= $h($c['hero']['headline_1']) ?><br />
        <span class="gradient-text"><?= $h($c['hero']['headline_2']) ?></span>
      </h1>
      <p class="hero-sub"><?= $h($c['hero']['subtext']) ?></p>
      <div class="hero-actions">
        <a href="#contact" class="btn btn-primary"><?= $h($c['hero']['cta_primary']) ?></a>
        <a href="#services" class="btn btn-ghost"><?= $h($c['hero']['cta_secondary']) ?></a>
      </div>
      <div class="hero-stats">
        <div class="stat">
          <span class="stat-num"><?= $h($c['hero']['stat_1_num']) ?></span>
          <span class="stat-label"><?= $h($c['hero']['stat_1_label']) ?></span>
        </div>
        <div class="stat-divider"></div>
        <div class="stat">
          <span class="stat-num"><?= $h($c['hero']['stat_2_num']) ?></span>
          <span class="stat-label"><?= $h($c['hero']['stat_2_label']) ?></span>
        </div>
        <div class="stat-divider"></div>
        <div class="stat">
          <span class="stat-num"><?= $h($c['hero']['stat_3_num']) ?></span>
          <span class="stat-label"><?= $h($c['hero']['stat_3_label']) ?></span>
        </div>
      </div>
    </div>
    <div class="scroll-indicator">
      <span>Scroll</span>
      <div class="scroll-line"></div>
    </div>
  </section>

  <div class="marquee-section">
    <div class="marquee-track"><div class="marquee-inner">
      <span>AI Integration</span><span class="sep">·</span>
      <span>Workflow Automation</span><span class="sep">·</span>
      <span>Custom LLM Development</span><span class="sep">·</span>
      <span>RAG Systems</span><span class="sep">·</span>
      <span>Agentic Pipelines</span><span class="sep">·</span>
      <span>API Development</span><span class="sep">·</span>
      <span>Rapid Prototyping</span><span class="sep">·</span>
      <span>AI Strategy</span><span class="sep">·</span>
      <span>AI Integration</span><span class="sep">·</span>
      <span>Workflow Automation</span><span class="sep">·</span>
      <span>Custom LLM Development</span><span class="sep">·</span>
      <span>RAG Systems</span><span class="sep">·</span>
      <span>Agentic Pipelines</span><span class="sep">·</span>
      <span>API Development</span><span class="sep">·</span>
      <span>Rapid Prototyping</span><span class="sep">·</span>
      <span>AI Strategy</span><span class="sep">·</span>
    </div></div>
  </div>

  <section class="section" id="services">
    <div class="container">
      <div class="section-header">
        <div class="section-tag"><?= $h($c['services']['tag']) ?></div>
        <h2 class="section-title"><?= nl2br($h($c['services']['title'])) ?></h2>
        <p class="section-sub"><?= $h($c['services']['subtitle']) ?></p>
      </div>
      <div class="services-grid">
        <?php
        $icons = [
          '<path d="M12 2L2 7l10 5 10-5-10-5z"/><path d="M2 17l10 5 10-5"/><path d="M2 12l10 5 10-5"/>',
          '<rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><path d="M14 17.5h7M17.5 14v7"/>',
          '<circle cx="12" cy="12" r="3"/><path d="M12 1v4M12 19v4M4.22 4.22l2.83 2.83M16.95 16.95l2.83 2.83M1 12h4M19 12h4M4.22 19.78l2.83-2.83M16.95 7.05l2.83-2.83"/>',
          '<path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>',
          '<polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/><polyline points="17 6 23 6 23 12"/>',
          '<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>',
        ];
        foreach ($c['services']['cards'] as $i => $card):
          $featured = !empty($card['featured']) ? ' service-card-featured' : '';
        ?>
        <div class="service-card<?= $featured ?>">
          <div class="service-icon">
            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><?= $icons[$i % count($icons)] ?></svg>
          </div>
          <h3><?= $h($card['title']) ?></h3>
          <p><?= $h($card['description']) ?></p>
          <?php if (!empty($card['items'])): ?>
          <ul class="service-list">
            <?php foreach ($card['items'] as $item): if (trim($item) === '') continue; ?>
            <li><?= $h($item) ?></li>
            <?php endforeach; ?>
          </ul>
          <?php endif; ?>
          <?php if ($featured): ?><div class="service-card-glow"></div><?php endif; ?>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <section class="section section-dark" id="why">
    <div class="why-glow"></div>
    <div class="container">
      <div class="why-layout">
        <div class="why-left">
          <div class="section-tag"><?= $h($c['why']['tag']) ?></div>
          <h2 class="section-title"><?= nl2br($h($c['why']['title'])) ?></h2>
          <p class="section-sub"><?= $h($c['why']['subtitle']) ?></p>
          <a href="#contact" class="btn btn-primary"><?= $h($c['why']['cta_label']) ?></a>
        </div>
        <div class="why-right">
          <?php foreach ($c['why']['cards'] as $card): ?>
          <div class="why-card">
            <div class="why-card-num"><?= $h($card['num']) ?></div>
            <h4><?= $h($card['title']) ?></h4>
            <p><?= $h($card['body']) ?></p>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  </section>

  <section class="section" id="process">
    <div class="container">
      <div class="section-header">
        <div class="section-tag"><?= $h($c['process']['tag']) ?></div>
        <h2 class="section-title"><?= nl2br($h($c['process']['title'])) ?></h2>
        <p class="section-sub"><?= $h($c['process']['subtitle']) ?></p>
      </div>
      <div class="process-steps">
        <?php foreach ($c['process']['steps'] as $step): ?>
        <div class="process-step">
          <div class="step-number"><?= $h($step['num']) ?></div>
          <div class="step-content">
            <h3><?= $h($step['title']) ?></h3>
            <p><?= $h($step['body']) ?></p>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <section class="section section-alt" id="results">
    <div class="container">
      <div class="section-header">
        <div class="section-tag"><?= $h($c['results']['tag']) ?></div>
        <h2 class="section-title"><?= nl2br($h($c['results']['title'])) ?></h2>
      </div>
      <div class="results-grid">
        <?php foreach ($c['results']['cards'] as $card): ?>
        <div class="result-card">
          <div class="result-num gradient-text"><?= $h($card['num']) ?></div>
          <div class="result-label"><?= $h($card['label']) ?></div>
          <p><?= $h($card['body']) ?></p>
        </div>
        <?php endforeach; ?>
      </div>
      <?php if (!empty($c['results']['capabilities'])): ?>
      <div class="capability-strip">
        <?php foreach ($c['results']['capabilities'] as $cap): if (trim($cap) === '') continue; ?>
        <div class="capability-item">
          <div class="cap-icon">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
          </div>
          <span><?= $h($cap) ?></span>
        </div>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>
    </div>
  </section>

  <section class="section cta-section" id="contact">
    <div class="cta-glow"></div>
    <div class="container">
      <div class="cta-inner">
        <div class="section-tag"><?= $h($c['contact']['tag']) ?></div>
        <h2 class="cta-title"><?= nl2br($h($c['contact']['title'])) ?></h2>
        <p class="cta-sub"><?= $h($c['contact']['subtitle']) ?></p>
        <form class="contact-form" id="contactForm">
          <div class="form-row">
            <div class="form-group"><input type="text" placeholder="Your Name" required /></div>
            <div class="form-group"><input type="email" placeholder="Work Email" required /></div>
          </div>
          <div class="form-group"><input type="text" placeholder="Company" /></div>
          <div class="form-group">
            <select>
              <option value="" disabled selected>What are you looking to build?</option>
              <?php foreach ($c['contact']['services_options'] as $opt): if (trim($opt) === '') continue; ?>
              <option><?= $h($opt) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-group"><textarea placeholder="Describe what you're trying to solve..." rows="4"></textarea></div>
          <button type="submit" class="btn btn-primary btn-full">Send Message</button>
        </form>
      </div>
    </div>
  </section>

  <footer class="footer">
    <div class="container footer-inner">
      <div class="footer-brand">
        <a href="#" class="logo">
          <span class="logo-mark"><?= $h(mb_substr($c['nav']['logo_text'], 0, 1)) ?></span>
          <span class="logo-text"><?= $h($c['nav']['logo_text']) ?></span>
        </a>
        <p><?= $h($c['footer']['brand_text']) ?></p>
      </div>
      <div class="footer-links">
        <div class="footer-col">
          <h5>Services</h5>
          <?php foreach (array_slice($c['services']['cards'], 0, 4) as $card): ?>
          <a href="#services"><?= $h($card['title']) ?></a>
          <?php endforeach; ?>
        </div>
        <div class="footer-col">
          <h5>Company</h5>
          <a href="#process">How We Work</a>
          <a href="#results">Results</a>
          <a href="#contact">Contact</a>
        </div>
      </div>
    </div>
    <div class="footer-bottom">
      <div class="container">
        <span><?= $h($c['footer']['copyright']) ?></span>
        <div class="footer-mono"><?= $h($c['footer']['domain']) ?></div>
      </div>
    </div>
  </footer>

  <script src="script.js"></script>
</body>
</html>
    <?php
    $html = ob_get_clean();
    $outputFile = __DIR__ . '/../index.html';
    return file_put_contents($outputFile, $html) !== false;
}
