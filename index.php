<?php session_start(); ?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>SkillConnect — Connect With Trusted Local Talent</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://cdn.jsdelivr.net/npm/daisyui@2.51.5/dist/full.css" rel="stylesheet">
</head>
<body class="bg-base-200 min-h-screen flex flex-col">

  <!-- Navbar -->
  <nav class="navbar bg-base-100 shadow sticky top-0 z-50">
    <div class="flex-1">
      <a href="index.php" class="btn btn-ghost normal-case text-xl font-bold">SkillConnect</a>
    </div>
    <div class="flex-none space-x-2">
      <?php if (!isset($_SESSION['user_id'])): ?>
        <a href="login.php" class="btn btn-outline btn-sm">Login</a>
        <a href="register.php" class="btn btn-primary btn-sm">Get Started</a>
      <?php else: ?>
        <a href="dashboard.php" class="btn btn-outline btn-sm">Dashboard</a>
        <a href="logout.php" class="btn btn-error btn-sm">Logout</a>
      <?php endif; ?>
    </div>
  </nav>

  <!-- Hero Section -->
  <section class="hero min-h-[85vh] bg-base-100">
    <div class="hero-content flex-col lg:flex-row-reverse">
      <img src="https://images.unsplash.com/photo-1568605114967-8130f3a36994?w=500&h=350&fit=crop" class="rounded-lg shadow-2xl" alt="SkillConnect workers">
      <div>
        <h1 class="text-5xl font-bold">Bridge the Gap Between Skill and Opportunity</h1>
        <p class="py-6 text-lg text-gray-600">
          SkillConnect connects individuals and businesses with verified skilled and unskilled laborers.
          Whether you need a carpenter, cleaner, or web developer — find trusted local talent in minutes.
        </p>
        <a href="register.php" class="btn btn-primary btn-lg">Get Started</a>
      </div>
    </div>
  </section>

  <!-- Features Section -->
  <section class="py-16 bg-base-200">
    <div class="max-w-6xl mx-auto text-center">
      <h2 class="text-3xl font-bold mb-8">Why Choose SkillConnect?</h2>
      <div class="grid md:grid-cols-3 gap-8">
        <div class="card bg-base-100 shadow">
          <div class="card-body">
            <h3 class="text-xl font-semibold mb-2">Verified Workers</h3>
            <p>All providers undergo a simple verification process for trust and safety.</p>
          </div>
        </div>
        <div class="card bg-base-100 shadow">
          <div class="card-body">
            <h3 class="text-xl font-semibold mb-2">Smart Matching</h3>
            <p>Our platform intelligently connects clients with the right professionals nearby.</p>
          </div>
        </div>
        <div class="card bg-base-100 shadow">
          <div class="card-body">
            <h3 class="text-xl font-semibold mb-2">Portfolio System</h3>
            <p>Providers can showcase past work, client reviews, and build a trusted profile.</p>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Call-to-Action -->
  <section class="bg-primary text-primary-content py-16 text-center">
    <h2 class="text-3xl font-bold mb-4">Ready to Get Started?</h2>
    <p class="mb-8">Join SkillConnect today and connect with trusted people in your area.</p>
    <div class="space-x-4">
      <a href="register.php?role=client" class="btn btn-outline btn-light">Hire Labor</a>
      <a href="register.php?role=provider" class="btn btn-accent">Offer Your Skills</a>
    </div>
  </section>

  <!-- Footer -->
  <footer class="footer p-10 bg-base-300 text-base-content mt-auto">
    <aside>
      <h3 class="text-xl font-bold">SkillConnect</h3>
      <p>Empowering trusted local connections.<br>© <?=date('Y')?> SkillConnect</p>
    </aside>
    <nav>
      <h6 class="footer-title">Links</h6>
      <a href="about.php" class="link link-hover">About Us</a>
      <a href="contact.php" class="link link-hover">Contact</a>
      <a href="register.php" class="link link-hover">Sign Up</a>
    </nav>
  </footer>

</body>
</html>
