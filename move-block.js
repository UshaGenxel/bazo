const fs = require('fs');
const path = require('path');

const getLatestFolder = (dir) => {
    const folders = fs.readdirSync(dir)
        .filter(name => fs.statSync(path.join(dir, name)).isDirectory())
        .filter(name => !['node_modules', 'blocks'].includes(name))
        .map(name => ({
            name,
            time: fs.statSync(path.join(dir, name)).mtime.getTime()
        }))
        .sort((a, b) => b.time - a.time);

    return folders[0]?.name || null;
};

const latestBlock = getLatestFolder('./');
if (latestBlock && latestBlock !== 'blocks') {
    const targetDir = path.join('blocks', latestBlock);

    fs.mkdirSync('blocks', { recursive: true });
    fs.renameSync(latestBlock, targetDir);
    console.log(`✅ Moved block "${latestBlock}" into ./blocks/${latestBlock}`);
} else {
    console.log('❌ No new block found to move.');
}
