import imageCompression from 'browser-image-compression';

const COMPRESSION_OPTIONS = {
    maxSizeMB: 0.5,
    maxWidthOrHeight: 800,
    useWebWorker: true,
    fileType: 'image/webp' as const
};

export async function compressToWebP(file: File): Promise<File> {
    const compressed = await imageCompression(file, COMPRESSION_OPTIONS);
    // Ensure .webp extension in filename
    const nameWithoutExt = file.name.replace(/\.[^.]+$/, '');
    return new File([compressed], `${nameWithoutExt}.webp`, { type: 'image/webp' });
}