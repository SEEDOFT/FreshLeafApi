<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>@yield('title')</title>

        <style>
            :root {
                color-scheme: light;
            }

            html,
            body {
                min-height: 100%;
                margin: 0;
            }

            body {
                background:
                    radial-gradient(circle at top right, rgba(46, 159, 88, 0.12), transparent 42%),
                    #f8faf8;
                color: #334038;
                display: flex;
                font-family: ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, 'Noto Sans', sans-serif, 'Apple Color Emoji', 'Segoe UI Emoji', 'Segoe UI Symbol', 'Noto Color Emoji';
                justify-content: center;
                align-items: center;
                min-height: 100svh;
                padding: clamp(14px, 3vw, 28px);
                text-align: center;
            }

            .error-shell {
                width: min(640px, 100%);
                border-radius: 18px;
                border: 1px solid #c5cfc7;
                background: #ffffff;
                box-shadow: 0 20px 40px rgba(22, 32, 24, 0.1);
                padding: clamp(18px, 4vw, 36px);
            }

            .error-title {
                color: #162018;
                font-size: clamp(1.25rem, 2.9vw, 2rem);
                font-weight: 650;
                line-height: 1.35;
                margin: 0;
                word-wrap: break-word;
                overflow-wrap: anywhere;
            }

            @media (max-height: 460px) and (orientation: landscape) {
                body {
                    align-items: flex-start;
                }

                .error-shell {
                    margin-top: 10px;
                    margin-bottom: 10px;
                }
            }

            @media (max-width: 479px) {
                body {
                    padding: 12px;
                }

                .error-shell {
                    border-radius: 14px;
                    padding: 16px;
                }
            }

            @media (prefers-reduced-motion: reduce) {
                * {
                    scroll-behavior: auto !important;
                }
            }

            @media (prefers-color-scheme: dark) {
                :root {
                    color-scheme: dark;
                }

                body {
                    background:
                        radial-gradient(circle at top right, rgba(46, 159, 88, 0.2), transparent 46%),
                        #101612;
                    color: #d3dbd5;
                }

                .error-shell {
                    background: #162018;
                    border-color: #334038;
                    box-shadow: 0 22px 40px rgba(0, 0, 0, 0.35);
                }

                .error-title {
                    color: #eef2ef;
                }
            }
        </style>
    </head>
    <body>
        <div class="error-shell">
            <h1 class="error-title">@yield('message')</h1>
        </div>
    </body>
</html>
