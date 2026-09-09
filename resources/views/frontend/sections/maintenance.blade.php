    <div class="maintenance-container">
        <div class="icon-wrapper">
            <!-- SVG Icon (Gears - example) -->
            <svg class="maintenance-icon" viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg">
                <g class="maintenance-icon-outer">
                    <path
                        d="M87.3,55.6c0.3-1.8,0.3-3.7,0-5.5l10.9-7.8c0.8-0.6,1-1.6,0.5-2.4l-5-8.7c-0.5-0.8-1.6-1.2-2.4-0.7L79,35.8 c-2.6-2-5.5-3.6-8.6-4.7l-1.7-12.3c-0.1-1-1-1.7-1.9-1.7H31.2c-1,0-1.8,0.7-1.9,1.7L27.6,29c-3.1,1.1-6,2.7-8.6,4.7L6.7,28.4 c-0.8-0.5-1.9-0.1-2.4,0.7l-5,8.7c-0.5,0.8-0.3,1.9,0.5,2.4l10.9,7.8c-0.3,1.8-0.3,3.7,0,5.5L-0.2,63.5c-0.8,0.6-1,1.6-0.5,2.4 l5,8.7c0.5,0.8,1.6,1.2,2.4,0.7l12.3-5.3c2.6,2,5.5,3.6,8.6,4.7l1.7,12.3c0.1,1,1,1.7,1.9,1.7h37.5c1,0,1.8-0.7,1.9-1.7 L70.8,72c3.1-1.1,6-2.7,8.6-4.7l12.3,5.3c0.8,0.5,1.9,0.1,2.4-0.7l5-8.7c0.5-0.8,0.3-1.9-0.5-2.4L87.3,55.6z M50,65.6 c-8.6,0-15.6-7-15.6-15.6S41.4,34.4,50,34.4S65.6,41.4,65.6,50S58.6,65.6,50,65.6z" />
                </g>
                <g class="maintenance-icon-inner" transform-origin="50 50"> <!-- Inner gear for different animation -->
                    <path
                        d="M62.8,52.9c0.1-1,0.1-1.9,0-2.9l5.5-3.9c0.4-0.3,0.5-0.8,0.2-1.2l-2.5-4.3c-0.2-0.4-0.8-0.6-1.2-0.3L59.1,43 c-1.3-1-2.8-1.8-4.3-2.4l-0.8-6.1c0-0.5-0.5-0.8-1-0.8h-6.1c-0.5,0-0.9,0.4-1,0.8l-0.8,6.1c-1.5,0.5-3,1.4-4.3,2.4l-5.7-2.7 c-0.4-0.2-1,0-1.2,0.3l-2.5,4.3c-0.2,0.4-0.1,1,0.2,1.2l5.5,3.9c-0.1,1-0.1,1.9,0,2.9l-5.5,3.9c-0.4,0.3-0.5,0.8-0.2,1.2 l2.5,4.3c0.2,0.4,0.8,0.6,1.2,0.3l5.7-2.7c1.3,1,2.8,1.8,4.3,2.4l0.8,6.1c0,0.5,0.5,0.8,1,0.8h6.1c0.5,0,0.9-0.4,1-0.8 l0.8-6.1c1.5-0.5,3-1.4,4.3-2.4l5.7,2.7c0.4,0.2,1,0,1.2-0.3l2.5-4.3c0.2-0.4,0.1-1-0.2-1.2L62.8,52.9z M50,57.8 c-4.3,0-7.8-3.5-7.8-7.8s3.5-7.8,7.8-7.8s7.8,3.5,7.8,7.8S54.3,57.8,50,57.8z" />
                </g>
            </svg>
        </div>

        <h1>Site Under Maintenance</h1>

        <div class="message">
            <p>We're currently performing some essential updates to improve your experience.</p>
            <p>Our team is working hard to get things back up and running as quickly as possible. We appreciate your
                patience!</p>
        </div>

        <div class="progress-bar-container">
            <div class="progress-bar"></div>
        </div>

    </div>

    <style>
        :root {
            --primary-color: #3498db;
            /* A nice blue */
            --secondary-color: #2c3e50;
            /* Darker blue/grey */
            --text-color: #333;
            --light-text-color: #555;
            --background-color: #f4f7f6;
            --container-bg: #ffffff;
            --icon-color: var(--primary-color);
        }

        body {
            margin: 0;
            font-family: 'Poppins', sans-serif;
            background-color: var(--background-color);
            color: var(--text-color);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
            box-sizing: border-box;
            text-align: center;
            overflow-x: hidden;
            /* Prevent horizontal scroll on small screens with animations */
        }

        .maintenance-container {
            background-color: var(--container-bg);
            padding: 40px 30px;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            max-width: 600px;
            width: 100%;
            animation: fadeInScaleUp 0.8s ease-out forwards;
        }

        @keyframes fadeInScaleUp {
            0% {
                opacity: 0;
                transform: translateY(20px) scale(0.95);
            }

            100% {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        .icon-wrapper {
            margin-bottom: 30px;
        }

        .maintenance-icon {
            width: 80px;
            height: 80px;
            fill: var(--icon-color);
            animation: spin 10s linear infinite;
        }

        .maintenance-icon-inner {
            animation: pulse 2s infinite ease-in-out;
        }


        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }

        @keyframes pulse {
            0% {
                transform: scale(0.9);
            }

            50% {
                transform: scale(1.05);
            }

            100% {
                transform: scale(0.9);
            }
        }


        h1 {
            font-size: 2.5em;
            /* Responsive font size base */
            color: var(--secondary-color);
            margin-top: 0;
            margin-bottom: 15px;
            font-weight: 700;
            line-height: 1.2;
        }

        p {
            font-size: 1.1em;
            color: var(--light-text-color);
            line-height: 1.7;
            margin-bottom: 25px;
        }

        .message {
            margin-bottom: 30px;
        }

        .progress-bar-container {
            width: 80%;
            margin: 0 auto 30px auto;
            background-color: #e0e0e0;
            border-radius: 25px;
            height: 10px;
            overflow: hidden;
            position: relative;
        }

        .progress-bar {
            width: 0;
            /* Start with 0 */
            height: 100%;
            background: linear-gradient(90deg, var(--primary-color), #5dade2);
            border-radius: 25px;
            animation: fillProgress 20s linear infinite alternate;
            /* Long animation for illusion */
        }

        @keyframes fillProgress {
            0% {
                width: 10%;
            }

            25% {
                width: 40%;
            }

            50% {
                width: 60%;
            }

            75% {
                width: 80%;
            }

            100% {
                width: 95%;
            }

            /* Never quite reaches 100% */
        }


        .brand-logo img {
            max-height: 40px;
            margin-top: 20px;
            opacity: 0.7;
        }

        .contact-info {
            font-size: 0.9em;
            color: #777;
        }

        .contact-info a {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 600;
        }

        .contact-info a:hover {
            text-decoration: underline;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            h1 {
                font-size: 2em;
            }

            p {
                font-size: 1em;
            }

            .maintenance-icon {
                width: 60px;
                height: 60px;
            }

            .maintenance-container {
                padding: 30px 20px;
            }
        }

        @media (max-width: 480px) {
            h1 {
                font-size: 1.8em;
            }

            p {
                font-size: 0.95em;
            }

            .progress-bar-container {
                width: 90%;
            }
        }
    </style>
