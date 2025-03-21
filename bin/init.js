#! /usr/bin/env node

/* eslint no-console: 0 */

/**
 * External dependencies
 */
const fs = require('fs');
const path = require('path');
const readline = require('readline');

/**
 * Define Constants
 */
const rl = readline.createInterface({
    input: process.stdin,
    output: process.stdout,
});
const info = {
    error: (message) => {
        return `\x1b[31m${message}\x1b[0m`;
    },
    success: (message) => {
        return `\x1b[32m${message}\x1b[0m`;
    },
    warning: (message) => {
        return `\x1b[33m${message}\x1b[0m`;
    },
    message: (message) => {
        return `\x1b[34m${message}\x1b[0m`;
    },
};
let fileContentUpdated = false;
let fileNameUpdated = false;
let themeCleanup = false;

const args = process.argv.slice(2);

if (0 === args.length) {
    rl.question('Would you like to setup the child theme? (Y/n) ', (answer) => {
        if ('n' === answer.toLowerCase()) {
            console.log(info.warning('\nChild Theme Setup Cancelled.\n'));
            process.exit(0);
        }
        rl.question('Enter child theme name (shown in WordPress admin)*: ', (themeName) => {
            rl.question('Enter parent theme template name (folder name of the parent theme)*: ', (templateName) => {
                const themeInfo = renderThemeDetails(themeName, templateName);
                rl.question('Confirm the Child Theme Details (Y/n) ', (confirm) => {
                    if ('n' === confirm.toLowerCase()) {
                        console.log(info.warning('\nChild Theme Setup Cancelled.\n'));
                        process.exit(0);
                    }
                    initTheme(themeInfo);
                    rl.question('Would you like to run the child theme cleanup? (Y/n) ', (cleanup) => {
                        if ('n' === cleanup.toLowerCase()) {
                            console.log(info.warning('\nExiting without running child theme cleanup.\n'));
                            process.exit(0);
                        }
                        runThemeCleanup();
                        rl.close();
                    });
                });
            });
        });
    });
} else if ((args.includes('--clean') || args.includes('-c')) && 1 === args.length) {
    rl.question('Would you like to run the child theme cleanup? (Y/n) ', (cleanup) => {
        if ('n' === cleanup.toLowerCase()) {
            console.log(info.warning('\nExiting without running child theme cleanup.\n'));
            process.exit(0);
        }
        runThemeCleanup();
        rl.close();
    });
} else {
    console.log(info.error('\nInvalid arguments.\n'));
    process.exit(0);
}

rl.on('close', () => {
    process.exit(0);
});

/**
 * Renders the theme setup modal with all necessary information related to the search-replace.
 *
 * @param {string} themeName
 * @param {string} templateName
 *
 * @return {Object} themeInfo
 */
const renderThemeDetails = (themeName, templateName) => {
    console.log(info.success('\nFiring up the child theme setup...'));

    // Bail out if theme name isn't provided.
    if (!themeName || !templateName) {
        console.log(info.error('\nChild Theme name and template name are required.\n'));
        process.exit(0);
    }

    // Generate theme info.
    const themeInfo = generateThemeInfo(themeName);
    themeInfo.templateName = templateName;

    const themeDetails = {
        'Theme Name': themeInfo.themeName,
        'Template': themeInfo.templateName.toLowerCase().replace(/\s+/g, '-'),
        'Theme Version': '1.0.0',
        'Text Domain': themeInfo.kebabCase,
        'Package': themeInfo.trainCase,
        'Namespace': themeInfo.pascalSnakeCase,
        'Function Prefix': themeInfo.snakeCaseWithUnderscoreSuffix,
        'CSS Class Prefix': themeInfo.kebabCaseWithHyphenSuffix,
        'PHP Variable Prefix': themeInfo.snakeCaseWithUnderscoreSuffix,
        'Version Constant': `${themeInfo.macroCase}_VERSION`,
        'Theme Directory Constant': `${themeInfo.macroCase}_TEMP_DIR`,
        'Theme Build Directory Constant': `${themeInfo.macroCase}_BUILD_DIR`,
        'Theme Build Directory URI Constant': `${themeInfo.macroCase}_BUILD_URI`,
    };

    // Calculate the longest key-value pair for formatting
    const biggestStringLength = Math.max(...Object.keys(themeDetails).map((key) => key.length + themeDetails[key].length));

    console.log(info.success('\nChild Theme Details:'));
    console.log(info.warning('┌' + '─'.repeat(biggestStringLength + 4) + '┐'));
    Object.keys(themeDetails).forEach((key) => {
        const value = themeDetails[key];
        console.log(info.warning('│ ' + info.success(key + ': ') + info.message(value) + ' '.repeat(biggestStringLength - (key.length + value.length)) + ' │'));
    });
    console.log(info.warning('└' + '─'.repeat(biggestStringLength + 4) + '┘'));

    return themeInfo;
};

/**
 * Initialize new theme
 *
 * @param {Object} themeInfo
 */
const initTheme = (themeInfo) => {
    const chunksToReplace = {
		'blank theme child': themeInfo.themeNameLowerCase,
		'Blank Theme Child': themeInfo.themeName,
		BlankThemeChild: themeInfo.pascalCase,
		'BLANK THEME CHILD': themeInfo.themeNameCobolCase,
		'blank-theme-child': themeInfo.kebabCase,
		'Blank-Theme-Child': themeInfo.trainCase,
		'BLANK-THEME-CHILD': themeInfo.cobolCase,
		blank_theme_child: themeInfo.snakeCase,
		Blank_Theme_Child: themeInfo.pascalSnakeCase,
		BLANK_THEME_CHILD: themeInfo.macroCase,
		'blank-theme-child-': themeInfo.kebabCaseWithHyphenSuffix,
		'Blank-Theme-Child-': themeInfo.trainCaseWithHyphenSuffix,
		'BLANK-THEME-CHILD-': themeInfo.cobolCaseWithHyphenSuffix,
		blank_theme_child_: themeInfo.snakeCaseWithUnderscoreSuffix,
		Blank_Theme_Child_: themeInfo.pascalSnakeCaseWithUnderscoreSuffix,
        BLANK_THEME_CHILD_: themeInfo.macroCaseWithUnderscoreSuffix,
        'parent-template': themeInfo.templateName.toLowerCase().replace(/\s+/g, '-'),
	};

    const files = getAllFiles(getRoot());

    // File name to replace in.
    const fileNameToReplace = {};
    files.forEach((file) => {
        const fileName = path.basename(file);
        Object.keys(chunksToReplace).forEach((key) => {
            if (fileName.includes(key)) {
                fileNameToReplace[fileName] = fileName.replace(key, chunksToReplace[key]);
            }
        });
    });

    // Replace files contents.
    console.log(info.success('\nUpdating child theme details in file(s)...'));
    Object.keys(chunksToReplace).forEach((key) => {
        replaceFileContent(files, key, chunksToReplace[key]);
    });
    if (!fileContentUpdated) {
        console.log(info.error('No file content updated.\n'));
    }

    // Replace file names
    console.log(info.success('\nUpdating child theme file name(s)...'));
    Object.keys(fileNameToReplace).forEach((key) => {
        replaceFileName(files, key, fileNameToReplace[key]);
    });
    if (!fileNameUpdated) {
        console.log(info.error('No file name updated.\n'));
    }

    if (fileContentUpdated || fileNameUpdated) {
        console.log(info.success('\nYour new child theme is ready to go!'), '✨');
        // Docs link
        console.log(info.success('\nFor more information on how to use this child theme, please visit the following link: ' + info.warning('https://github.com/rabindratharu/blank-theme/blob/main/README.md\n')));
    } else {
        console.log(info.warning('\nNo changes were made to your child theme.\n'));
    }
};

/**
 * Get all files in a directory
 *
 * @param {Array} dir - Directory to search
 */
const getAllFiles = (dir) => {
    const dirOrFilesIgnore = [
        '.git',
        '.github',
        'node_modules',
        'vendor',
    ];

    try {
        let files = fs.readdirSync(dir);
        files = files.filter((fileOrDir) => !dirOrFilesIgnore.includes(fileOrDir));

        const allFiles = [];
        files.forEach((file) => {
            const filePath = path.join(dir, file);
            const stat = fs.statSync(filePath);
            if (stat.isDirectory()) {
                allFiles.push(...getAllFiles(filePath));
            } else {
                allFiles.push(filePath);
            }
        });
        return allFiles;
    } catch (err) {
        console.log(info.error(err));
    }
};

/**
 * Replace content in file
 *
 * @param {Array}  files           Files to search
 * @param {string} chunksToReplace String to replace
 * @param {string} newChunk        New string to replace with
 */
const replaceFileContent = (files, chunksToReplace, newChunk) => {
    files.forEach((file) => {
        const filePath = path.resolve(getRoot(), file);

        try {
            let content = fs.readFileSync(filePath, 'utf8');
            const regex = new RegExp(chunksToReplace, 'g');
            content = content.replace(regex, newChunk);
            if (content !== fs.readFileSync(filePath, 'utf8')) {
                fs.writeFileSync(filePath, content, 'utf8');
                console.log(info.success(`Updated [${info.message(chunksToReplace)}] ${info.success('to')} [${info.message(newChunk)}] ${info.success('in file')} [${info.message(path.basename(file))}]`));
                fileContentUpdated = true;
            }
        } catch (err) {
            console.log(info.error(`\nError: ${err}`));
        }
    });
};

/**
 * Change File Name
 *
 * @param {Array}  files       Files to search
 * @param {string} oldFileName Old file name
 * @param {string} newFileName New file name
 */
const replaceFileName = (files, oldFileName, newFileName) => {
    files.forEach((file) => {
        if (oldFileName !== path.basename(file)) {
            return;
        }
        const filePath = path.resolve(getRoot(), file);
        const newFilePath = path.resolve(getRoot(), file.replace(oldFileName, newFileName));
        try {
            fs.renameSync(filePath, newFilePath);
            console.log(info.success(`Updated file [${info.message(path.basename(filePath))}] ${info.success('to')} [${info.message(path.basename(newFilePath))}]`));
            fileNameUpdated = true;
        } catch (err) {
            console.log(info.error(`\nError: ${err}`));
        }
    });
};

/**
 * Generate Theme Info from Theme Name
 *
 * @param {string} themeName
 */
const generateThemeInfo = (themeName) => {
    const themeNameLowerCase = themeName.toLowerCase();

    const kebabCase = themeName.replace(/\s+/g, '-').toLowerCase();
    const snakeCase = kebabCase.replace(/\-/g, '_');
    const kebabCaseWithHyphenSuffix = kebabCase + '-';
    const snakeCaseWithUnderscoreSuffix = snakeCase + '_';

    const trainCase = kebabCase.replace(/\b\w/g, (l) => {
        return l.toUpperCase();
    });
    const pascalCase = trainCase.replace(/-/g, '');
    const themeNameTrainCase = trainCase.replace(/\-/g, ' ');
    const pascalSnakeCase = trainCase.replace(/\-/g, '_');
    const trainCaseWithHyphenSuffix = trainCase + '-';
    const pascalSnakeCaseWithUnderscoreSuffix = pascalSnakeCase + '_';

    const cobolCase = kebabCase.toUpperCase();
    const themeNameCobolCase = themeNameTrainCase.toUpperCase();
    const macroCase = snakeCase.toUpperCase();
    const cobolCaseWithHyphenSuffix = cobolCase + '-';
    const macroCaseWithUnderscoreSuffix = macroCase + '_';

    return {
        themeName,
        themeNameLowerCase,
        kebabCase,
        snakeCase,
        kebabCaseWithHyphenSuffix,
        snakeCaseWithUnderscoreSuffix,
        trainCase,
        pascalCase,
        themeNameTrainCase,
        pascalSnakeCase,
        trainCaseWithHyphenSuffix,
        pascalSnakeCaseWithUnderscoreSuffix,
        cobolCase,
        themeNameCobolCase,
        macroCase,
        cobolCaseWithHyphenSuffix,
        macroCaseWithUnderscoreSuffix,
    };
};

/**
 * Return root directory
 *
 * @return {string} root directory
 */
const getRoot = () => {
    return path.resolve(__dirname, '../');
};

/**
 * Run theme cleanup to delete files and directories
 *
 * It will remove following directories and files:
 * 1. .git
 * 2. .github
 * 3. bin
 */
const runThemeCleanup = () => {
    const deleteDirs = [
        '.git',
        '.github',
        'bin'
    ];

    deleteDirs.forEach((dir) => {
        const dirPath = path.resolve(getRoot(), dir);
        try {
            if (fs.existsSync(dirPath)) {
                fs.rmSync(dirPath, { recursive: true, force: true });
                console.log(info.success(`Deleted directory [${info.message(dir)}]`));
                themeCleanup = true;
            }
        } catch (err) {
            console.log(info.error(`\nError: ${err}`));
        }
    });

    if (themeCleanup) {
        console.log(info.success('\nChild Theme cleanup completed!'), '✨');
    } else {
        console.log(info.warning('\nNo child theme cleanup required!\n'));
    }
};