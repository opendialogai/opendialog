<script>
export default {
  name: 'pager',
  data() {
    return {
      currentPage: 1,
      totalPages: 1,
    };
  },
  methods: {
    showPageNumber(pageNumber) {
      if (pageNumber == 1 || pageNumber == this.totalPages) return true;
      if (pageNumber > this.currentPage - 3 && pageNumber < this.currentPage + 3) return true;

      return false;
    },
    showPageEllipsis(pageNumber) {
      if (pageNumber > 1 && pageNumber == this.currentPage - 3) return true;
      if (pageNumber < this.totalPages && pageNumber == this.currentPage + 3) return true;

      return false;
    },
  },
};
</script>
