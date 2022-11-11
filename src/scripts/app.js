
import { chuckTasks } from './chuckTasks';

/**
 * app.main
 */
const main = async (err) => {
  if (err) {
    // handle hmr errors
    console.error(err);
  }

  document.addEventListener('DOMContentLoaded', () => {
    // application code
    chuckTasks()
  })

};

/**
 * Initialize
 *
 * @see https://webpack.js.org/api/hot-module-replacement
 */
main();
import.meta.webpackHot?.accept(main);
