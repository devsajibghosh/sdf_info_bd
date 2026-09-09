  <div class="coming-soon-wrapper py-5">
      <div class="container text-center">
          <div class="coming-soon-content">
              {{-- You can place a logo here if you have one --}}
              {{-- <img src="{{ asset('path/to/your/logo.png') }}" alt="Your Logo" class="logo mb-4"> --}}
              <i class="fas fa-rocket fa-3x icon-accent mb-3"></i> {{-- Example Icon (Font Awesome) --}}

              <h1 class="display-4 font-weight-bold mb-3 text-primary">We're Launching Soon!</h1>
              <p class="lead text-muted mb-4">
                  Our team is working hard to bring you an amazing new experience.
                  Get ready for something exciting!
              </p>

              <div id="countdown" class="countdown-timer mb-5">
                  <div class="timer-box">
                      <span id="days" class="timer-value">00</span>
                      <span class="timer-label">Days</span>
                  </div>
                  <div class="timer-box">
                      <span id="hours" class="timer-value">00</span>
                      <span class="timer-label">Hours</span>
                  </div>
                  <div class="timer-box">
                      <span id="minutes" class="timer-value">00</span>
                      <span class="timer-label">Minutes</span>
                  </div>
                  <div class="timer-box">
                      <span id="seconds" class="timer-value">00</span>
                      <span class="timer-label">Seconds</span>
                  </div>
              </div>

              <div class="social-icons mt-5">
                  <p class="text-muted mb-2">@lang('Follow us for updates'):</p>
                  <a href="#" class="social-icon"><i class="fab fa-facebook-f"></i></a>
                  <a href="#" class="social-icon"><i class="fab fa-twitter"></i></a>
                  <a href="#" class="social-icon"><i class="fab fa-instagram"></i></a>
                  <a href="#" class="social-icon"><i class="fab fa-linkedin-in"></i></a>
              </div>
          </div>
      </div>
  </div>



  @push('styles')
      {{-- If you're using Font Awesome, make sure it's included in your main layout or add the CDN link here --}}
      {{-- <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css"> --}}
      <style>
          /* Ensure the main layout and its parent containers allow for full height if you want a full-page coming soon */
          /* For this example, we assume it's part of a larger site. */

          .coming-soon-wrapper {
              display: flex;
              align-items: center;
              justify-content: center;
              min-height: calc(100vh - 120px);
              /* Adjust 120px based on your header/footer height */
              background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
              font-family: 'Arial', sans-serif;
              /* Or your preferred font */
              padding: 40px 15px;
              /* py-60 from original is fine, this is just an example */
          }

          .coming-soon-content {
              background-color: #ffffff;
              padding: 40px 30px;
              border-radius: 10px;
              box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
              max-width: 700px;
              margin: auto;
          }

          .coming-soon-content .logo {
              max-height: 80px;
              margin-bottom: 20px;
          }

          .coming-soon-content .icon-accent {
              color: #007bff;
              /* Bootstrap primary color, change as needed */
          }

          .coming-soon-content h1 {
              color: #333;
              /* If you use a specific primary color, define it: */
              /* color: #007bff; */
          }

          .countdown-timer {
              display: flex;
              justify-content: center;
              gap: 15px;
              /* Space between timer boxes */
          }

          .timer-box {
              background-color: #f8f9fa;
              /* Light background for timer boxes */
              padding: 15px;
              border-radius: 8px;
              min-width: 80px;
              box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
          }

          .timer-value {
              font-size: 2.5rem;
              font-weight: bold;
              color: #007bff;
              /* Or your primary color */
              display: block;
              line-height: 1;
          }

          .timer-label {
              font-size: 0.9rem;
              color: #6c757d;
              /* Muted text color */
              display: block;
              text-transform: uppercase;
          }

          .subscribe-form .form-control-lg {
              border-radius: 5px 0 0 5px;
              /* Rounded left corners */
          }

          .subscribe-form .btn-lg {
              border-radius: 0 5px 5px 0;
              /* Rounded right corners */
          }

          .subscribe-form .form-control:focus {
              border-color: #007bff;
              box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, .25);
          }

          .social-icons a.social-icon {
              color: #555;
              font-size: 1.5rem;
              margin: 0 10px;
              transition: color 0.3s ease;
          }

          .social-icons a.social-icon:hover {
              color: #007bff;
              /* Or your primary color */
          }

          /* Responsive adjustments */
          @media (max-width: 576px) {
              .coming-soon-content h1 {
                  font-size: 2.5rem;
              }

              .countdown-timer {
                  flex-wrap: wrap;
                  /* Allow timer boxes to wrap on small screens */
              }

              .timer-box {
                  min-width: 70px;
                  padding: 10px;
                  margin-bottom: 10px;
                  /* Add some space when they wrap */
              }

              .timer-value {
                  font-size: 1.8rem;
              }

              .subscribe-form {
                  flex-direction: column;
                  align-items: stretch;
              }

              .subscribe-form .form-group {
                  margin-right: 0 !important;
                  margin-bottom: 10px !important;
              }

              .subscribe-form .form-control-lg,
              .subscribe-form .btn-lg {
                  border-radius: 5px;
                  /* Full rounded corners on mobile */
              }
          }
      </style>
  @endpush

  @push('scripts')
      <script>
          // Set the date we're counting down to
          // Example: January 1, 2025 00:00:00
          // IMPORTANT: JavaScript month is 0-indexed (0 for January, 11 for December)
          var countDownDate = new Date("Jun 10, 2025 00:00:00").getTime();
          // Or, for example, 30 days from now:
          // var countDownDate = new Date().getTime() + (30 * 24 * 60 * 60 * 1000);


          // Update the count down every 1 second
          var x = setInterval(function() {

              // Get today's date and time
              var now = new Date().getTime();

              // Find the distance between now and the count down date
              var distance = countDownDate - now;

              // Time calculations for days, hours, minutes and seconds
              var days    = Math.floor(distance / (1000 * 60 * 60 * 24));
              var hours   = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
              var minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
              var seconds = Math.floor((distance % (1000 * 60)) / 1000);

              // Display the result in the elements
              document.getElementById("days").innerHTML = String(days).padStart(2, '0');
              document.getElementById("hours").innerHTML = String(hours).padStart(2, '0');
              document.getElementById("minutes").innerHTML = String(minutes).padStart(2, '0');
              document.getElementById("seconds").innerHTML = String(seconds).padStart(2, '0');

              // If the count down is finished, write some text
              if (distance < 0) {
                  clearInterval(x);
                  document.getElementById("countdown").innerHTML =
                      "<div class='w-100 text-center'><h3 class='text-success'>We Are Live!</h3></div>";
                  // You might want to hide the subscribe form or change the message
                  var subscribeForm = document.querySelector('.subscribe-form');
                  if (subscribeForm) subscribeForm.style.display = 'none';
                  var subscribePrompt = document.querySelector('.subscribe-form ~ p');
                  if (subscribePrompt) subscribePrompt.textContent = 'Check out our new site!';

              }
          }, 1000);

          // Optional: Handle form submission with AJAX if you don't want a page reload
          // For this example, it's a standard form submission.
          // If you want to implement AJAX, you'd do something like:
          /*
          const subscribeForm = document.querySelector('.subscribe-form');
          if (subscribeForm) {
              subscribeForm.addEventListener('submit', function(e) {
                  e.preventDefault();
                  const email = this.querySelector('input[name="email"]').value;
                  // console.log('Subscribing with:', email);
                  // Here you would typically use fetch() or XMLHttpRequest to send data to your backend
                  // fetch('/subscribe-endpoint', {
                  //     method: 'POST',
                  //     headers: {
                  //         'Content-Type': 'application/json',
                  //         'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
                  //     },
                  //     body: JSON.stringify({ email: email })
                  // })
                  // .then(response => response.json())
                  // .then(data => {
                  //     if(data.success) {
                  //         // Show success message
                  //     } else {
                  //         // Show error message
                  //     }
                  // })
                  // .catch(error => console.error('Error:', error));
                  alert('Thank you for subscribing! (This is a demo, no email was sent)');
                  this.reset();
              });
          }
          */
      </script>
  @endpush
