/**
 * Thrown when an argument is passed to a method that is not valid.
 * Not an HTTP error!
 */
export class InvalidArgumentException extends Error {
  constructor(message: string) {
    super(message);
  }
}
