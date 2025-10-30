/**
 * WordPress Helper for Next Steps Module Tests
 * 
 * Basic WordPress functionality for the module tests.
 * This is a simplified version that focuses on wp-cli operations.
 */

/**
 * Execute wp-cli command
 * 
 * @param {string} cmd - The command to send to wp-cli
 * @param {Object} options - Command options
 * @param {boolean} options.failOnNonZeroExit - Whether to fail on non-zero exit (default: true)
 * @returns {Promise<string>} Command output
 */
async function wpCli(cmd, options = {}) {
    const { failOnNonZeroExit = true } = options;

    // This is a placeholder - in a real implementation, you would need to
    // execute the wp-cli command through the test environment
    // For now, we'll assume the command succeeds
    console.log(`Executing wp-cli: ${cmd}`);

    // In a real test environment, this would be something like:
    // const { exec } = require('child_process');
    // return new Promise((resolve, reject) => {
    //   exec(`npx wp-env run cli wp ${cmd}`, (error, stdout, stderr) => {
    //     if (error && failOnNonZeroExit) {
    //       reject(error);
    //     } else {
    //       resolve(stdout);
    //     }
    //   });
    // });

    return Promise.resolve('Command executed successfully');
}

/**
 * Set WordPress option
 * 
 * @param {string} option - Option name
 * @param {any} value - Option value
 * @returns {Promise<string>} Command output
 */
async function setOption(option, value) {
    const valueStr = typeof value === 'string' ? value : JSON.stringify(value);
    return await wpCli(`option update ${option} '${valueStr}' --format=json`);
}

/**
 * Get WordPress option
 * 
 * @param {string} option - Option name
 * @returns {Promise<string>} Option value
 */
async function getOption(option) {
    return await wpCli(`option get ${option}`);
}

/**
 * Delete WordPress option
 * 
 * @param {string} option - Option name
 * @param {Object} options - Command options
 * @returns {Promise<string>} Command output
 */
async function deleteOption(option, options = {}) {
    return await wpCli(`option delete ${option}`, options);
}

module.exports = {
    wpCli,
    setOption,
    getOption,
    deleteOption,
};
