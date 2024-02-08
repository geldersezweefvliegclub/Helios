/**
 * Used in testing to provide for a TypeORM repository.
 */
export const mockRepository = jest.fn(() => ({
  metadata: {
    columns: [],
    relations: [],
  },
}));
