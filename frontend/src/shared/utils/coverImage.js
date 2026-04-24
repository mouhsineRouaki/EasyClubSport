import defaultCoverImage from '@/assets/Background.jpg'

export const resolveCoverImage = (...sources) => {
  const values = sources.flat(Infinity)
  const image = values.find((value) => typeof value === 'string' && value.trim())

  return image || defaultCoverImage
}

export const createCoverBackground = (...sources) => {
  const image = resolveCoverImage(...sources)

  return `linear-gradient(180deg, rgba(7, 16, 58, 0.12), rgba(7, 16, 58, 0.78)), url(${image})`
}

export { defaultCoverImage }
