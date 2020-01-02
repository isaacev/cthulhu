def grammar_url
  "#{Jekyll.sites.first.baseurl}/grammar"
end

def nl(n = 1)
  "\n" * n
end

def sp(n = 1)
  ' ' * n
end

def nonterm_with_link(name)
  "<a href=\"#{grammar_url()}##{name}\">#{name}</a>"
end

def span_with_class(name, inner)
  "<span class=\"#{name}\">#{inner}</span>"
end

module Grammar
  def self.parse(prods)
    prods.map { |name, prod| [name, parse_as_prod(name, prod)] }.to_h
  end

  def self.parse_as_prod(name, prod)
    Production.new(name, prod.map { |form| parse_as_seq(form) })
  end

  def self.parse_as_seq(seq)
    Sequence.new(seq.map { |node| parse_as_inline(node) })
  end

  def self.parse_as_inline(node)
    if node.key?('or')
      choices = node['or'].map { |choice| parse_as_seq(choice) }
      Choice.new(choices)
    elsif node.key?('option')
      limit = Repeater::ZERO_OR_ONE
      child = parse_as_seq(node['option'])
      Repeater.new(limit, child)
    elsif node.key?('star')
      limit = Repeater::ZERO_OR_MORE
      child = parse_as_seq(node['star'])
      Repeater.new(limit, child)
    elsif node.key?('plus')
      limit = Repeater::ONE_OR_MORE
      child = parse_as_seq(node['plus'])
      Repeater.new(limit, child)
    elsif node.key?('nonterminal')
      NonTerminal.new(node['nonterminal'])
    elsif node.key?('range')
      from, to = node['range']
      Range.new(Literal.new(from), Literal.new(to))
    elsif node.key?('literal')
      Literal.new(node['literal'])
    else
      raise "unknown grammar part: '#{node}'"
    end
  end

  class Node
    # empty
  end

  class Production < Node
    def initialize(name, forms)
      @name = name
      @forms = forms
    end

    def min_indent
      @name.length
    end

    def render(opts = {})
      indent = [opts.fetch(:indent, 0), @name.length].max
      is_def = opts.fetch(:is_def, false)
      out = span_with_class(
        'nonterminal',
        nonterm_with_link(@name)
      )
      out += sp(indent - @name.length)
      out += sp() + span_with_class('sym defined-as', '::=') + sp()
      out += @forms.first.render(opts)
      out += nl()
      @forms.drop(1).each do |form|
        out += sp(indent)
        out += sp(3) + span_with_class('sym or', '|') + sp()
        out += form.render(opts)
        out += nl()
      end

      if is_def
        "<span id=\"#{@name}\" class=\"production\" tabindex=0>#{out}</span>"
      else
        span_with_class('production', out)
      end
    end
  end

  class Choice < Node
    def initialize(choices)
      @choices = choices
    end

    def render(opts = {})
      span_with_class(
        'choice',
        @choices.map { |choice| choice.render(opts) }.join(' | ')
      )
    end
  end

  class Repeater < Node
    ZERO_OR_ONE  = '?'
    ZERO_OR_MORE = '*'
    ONE_OR_MORE  = '+'

    def initialize(limit, child)
      @limit = limit
      @child = child
    end

    def render(opts = {})
      span_with_class(
        'repeater',
        span_with_class('sym lparen', '(') +
          sp() +
          @child.render(opts) +
          sp() +
          span_with_class('sym rparen', ')') +
          span_with_class('repeater', @limit)
      )
    end
  end

  class Sequence < Node
    def initialize(parts)
      @parts = parts
    end

    def render(opts = {})
      out = ''
      @parts.each_with_index do |part, i|
        if i > 0
          out += sp()
        end
        out += part.render(opts)
      end
      span_with_class('sequence', out)
    end
  end

  class Range < Node
    def initialize(from, to)
      @from = from
      @to = to
    end

    def render(opts = {})
      span_with_class('range', @from.render(opts) +
        sp() +
        span_with_class('sym ellipsis', '&hellip;') +
        sp() +
        @to.render(opts))
    end
  end

  class NonTerminal < Node
    def initialize(name)
      @name = name
    end

    def render(opts = {})
      span_with_class('nonterminal', nonterm_with_link(@name))
    end
  end

  class Literal < Node
    def initialize(value)
      @value = value
    end

    def render(opts = {})
      span_with_class('literal', if @value == '"'
        '`"`'
      else
        "\"#{@value}\""
      end)
    end
  end
end

def get_parsed_grammar
  Grammar::parse(Jekyll.sites.first.data['grammar']['rules'])
end

class GrammarTag < Liquid::Tag
  def initialize(tag_name, input, tokens)
    @nonterms = input.strip.gsub(/\s+/m, ' ').split(' ')
    super
  end

  def render(ctx)
    grammar = get_parsed_grammar()
    prods = if @nonterms.empty?
      grammar.values
    else
      @nonterms.map do |nonterm|
        grammar.fetch(nonterm)
      end
    end

    opts = {
      :indent => prods.map { |p| p.min_indent() }.max,
      :is_def => @nonterms.empty?
    }

    prods = prods.map { |prod| prod.render(opts) }
    '<pre class="grammar"><code>' + prods.join('') + '</code></pre>'
  end
end

Liquid::Template.register_tag('grammar', GrammarTag)
