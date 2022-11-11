// @ts-check

/**
 * Build configuration
 *
 * @see {@link https://bud.js.org/guides/configure}
 * @param {import('@roots/bud').Bud} 
 */
 export default async (bud) => {
    bud
        /**
             * Set the project source directory
             */
        .setPath(`@src`, `src`)

        /**
         * Set the application entrypoints
         * These paths are expressed relative to the `@src` directory
         */
        .entry({
            app: [`scripts/app.js`, `styles/app.scss`],
        })

      /**
       * Matched files trigger a page reload when modified
       */
      .watch(["src//*/*"])
  
      /**
       * Proxy origin
       */
      .proxy("http://chuck.lndo.site")
  
      /**
       * Development origin
       */
      .serve("http://0.0.0.0:3009")
  
      /**
       * URI of the `public` directory
       */
      .setPublicPath("/")
  };
  